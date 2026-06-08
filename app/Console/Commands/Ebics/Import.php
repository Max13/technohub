<?php

namespace App\Console\Commands\Ebics;

use App\Jobs\Accounting\MatchRelatedParties;
use App\Models\Accounting\TransactionType;
use App\Models\Bank\Transaction;
use App\Services\Ebics;
use Carbon\CarbonPeriodImmutable;
use EbicsApi\Ebics\Exceptions\NoDownloadDataAvailableException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Import extends Command
{
    /** @var string $signature */
    protected $signature = 'ebics:import 
                            {from? : Start date of the statement (Y-m-d). Defaults to today}
                            {to? : End date of the statement (Y-m-d). Defaults to the "from" date}
                            {--show : Show the statements instead of importing them}';

    /** @var string $description */
    protected $description = 'Import bank statements using EBICS protocol, for the given date range';

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
                $from = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
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
                $to = Carbon::createFromFormat('Y-m-d', $to)->startOfDay();
                throw_if($to->lt($from) || $to->gt($today));
            } else {
                $to = $from;
            }
        } catch (Throwable $e) {
            $this->error('"to" date must be in the format Y-m-d, and be greater than or equal to "from". "' . $this->argument('to') . '" given');
            return self::FAILURE;
        }

        if ($from->isToday() && now()->hour <= 3) {
            $this->error('You cannot import statements for today, they may not be available/complete yet.');
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Importing statements, day by day, from %s to %s...',
            $from->toDateString(),
            $to->toDateString()
        ));

        $transactions = collect();
        foreach (CarbonPeriodImmutable::create($from, 'P1D', $to) as $date) {
            $this->output->write('<info>'.$date->toDateString().': </info>');
            /** @var \Illuminate\Support\Collection $statements */
            $statements = cache()->rememberForever(
                'ebics.statements.'.$date->toDateString(),
                function () use ($ebics, $date) {
                    $stFile = 'ebics/'.$date->format('Y/m/d').'.json';
                    try {
                        if (Storage::exists($stFile)) {
                            $st = collect(json_decode(Storage::get($stFile), true));
                        } else {
                            $st = $ebics->getStatements($date, $date);
                        }
                    } catch (NoDownloadDataAvailableException $e) {
                        $st = collect();
                    }

                    Storage::put($stFile, $st->toJson(JSON_PRETTY_PRINT));

                    return $st;
                }
            );

            if ($statements->isEmpty()) {
                $this->warn('No data');
                continue;
            }

            $this->newLine();

            $statement = $statements->first();
            $i = 1;
            $this->withProgressBar($statements->first()['BkToCstmrStmt']['Stmt']['Ntry'], function ($bkTrx) use (&$transactions, $statement, &$i) {
                // Ignore non-payment transactions
                if ($bkTrx['BkTxCd']['Domn']['Cd'] !== 'PMNT') {
                    return;
                }

                $tmp = Transaction::fromBankTransaction($bkTrx);
                if (is_null($tmp->uid)) {
                    $tmp->uid = $statement['BkToCstmrStmt']['Stmt']['Id'] . '-' . $i++;
                }

                try {
                    $trx = Transaction::withTrashed()->where('uid', $tmp->uid)->sole();
                    $trx->forceFill($tmp->toArray());
                } catch (Throwable $e) {
                    $trx = $tmp;
                }

                if ($trx->shouldBeIgnored()) {
                    return;
                }

                if ($this->option('show')) {
                    $transactions->push([
                        'created_at' => $trx->created_at->toDateString(),
                        'amount' => $trx->amount,
                        'type' => $trx->type?->value,
                        'dispute' => $trx->dispute_type?->value,
                        'related' => value(function ($trx) {
                            if ($trx->user) {
                                return $trx->user->fullname . ' ✓';
                            }
                            if ($trx->potential_students) {
                                return implode("\n", array_map(fn ($s) => $s, $trx->potential_students));
                            }
                            if ($trx->related_parties) {
                                return implode("\n", $trx->related_parties ?? []);
                            }
                            return null;
                        }, $trx),
                        'reference' => $trx->details,
                    ]);
                } elseif ($trx->isDirty()) { // Ignore duplicate transactions
                    $trx->save();

                    if (
                        $trx->amount > 0
                        || (
                                $trx->amount < 0
                             && in_array(
                                    $trx->type,
                                    [
                                        TransactionType::DISPUTE,
                                        TransactionType::WIRE_TRANSFER
                                    ],
                                    true
                                )
                        )
                    ) {
                        MatchRelatedParties::dispatchSync($trx);
                    }
                }
            });
            $this->newLine();
        }
        $this->newLine();

        if ($this->option('show')) {
            $this->table(
                ['Date', 'Amount', 'Type', 'Dispute', 'Related', 'Reference'],
                $transactions->toArray()
            );
        }

        return self::SUCCESS;
    }
}
