<?php

namespace App\Console\Commands\Ebics;

use App\Services\Ebics;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class Fetch extends Command
{
    /** @var string $signature */
    protected $signature = 'ebics:fetch 
                            {from? : Start date of the statement (Y-m-d). Defaults to today}
                            {to? : End date of the statement (Y-m-d). Defaults to the "from" date}';

    /** @var string $description */
    protected $description = 'Fetch bank\'s statements using EBICS protocol';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Ebics $ebics
     * @return int
     */
    public function handle(Ebics $ebics): int
    {
        $today = today();

        try {
            if (($from = $this->argument('from')) !== null) {
                $from = Carbon::createFromFormat('Y-m-d', $from);
                throw_if($from->gt($today));
            } else {
                $from = $today;
            }
        } catch (Throwable $e) {
            $this->error('"from" date must be in the format Y-m-d, and in the past. "' . $this->argument('from') . '" given');
            return self::FAILURE;
        }

        try {
            if (($to = $this->argument('to')) !== null) {
                $to = Carbon::createFromFormat('Y-m-d', $to);
                throw_if($to->lt($from) || $to->gt($today));
            } else {
                $to = $from;
            }
        } catch (Throwable $e) {
            $this->error('"to" date must be in the format Y-m-d, and be greater than or equal to "from". "' . $this->argument('to') . '" given');
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Fetching statements from %s to %s...',
            $from->toDateString(),
            $to->toDateString()
        ));

        /** @var \Illuminate\Support\Collection $statements */
        $statements = cache()->remember(
            'ebics.statements.'.$from->toDateString().':'.$to->toDateString(),
            today()->addWeek(),
            function () use ($ebics, $from, $to) {
                return $ebics->getStatements($from, $to);
            }
        );
        $rows = [];
        $this->withProgressBar($statements->pluck('BkToCstmrStmt.Stmt.Ntry')->flatten(1), function ($statement) use (&$rows) {
            $rows[] = [
                'date' => $statement->ValDt->Dt,
                'amount' => floatval($statement->Amt) * ($statement->CdtDbtInd === 'CRDT' ? 1 : -1),
                'operation' => $statement->BkTxCd->Domn->Cd . '-' . $statement->BkTxCd->Domn->Fmly->Cd . '-' . $statement->BkTxCd->Domn->Fmly->SubFmlyCd,
                'rtrinf' => data_get($statement, 'NtryDtls.TxDtls.RtrInf.Rsn.Cd'),
                'parties' => data_get($statement, 'NtryDtls.TxDtls.RltdPties.'.($statement->CdtDbtInd === 'CRDT' ? 'Cdtr' : 'Dbtr').'.Nm'),
                'reference' => preg_replace('/ {2,}/', ' ', $statement->NtryDtls->TxDtls->AddtlTxInf),
            ];
        });

        $this->table(
            ['Date', 'Amount', 'Operation', 'RtrInf', 'Related', 'Reference'],
            $rows
        );

        return self::SUCCESS;
    }
}
