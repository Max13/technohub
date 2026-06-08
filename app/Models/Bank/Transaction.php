<?php

namespace App\Models\Bank;

use App\Models\Accounting\DisputeType;
use App\Models\Accounting\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** {@inheritdoc} */
    protected $table = 'bank_transactions';

    /** {@inheritdoc} */
    protected $attributes = [
        'is_queued' => true,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_queued' => 'boolean',
        'nb_of_transactions' => 'integer',
        'potential_students' => 'array',
        'type' => TransactionType::class,
        'dispute_type' => DisputeType::class,
        'related_parties' => 'array',
    ];

    /** {@inheritdoc} */
    protected $fillable = [
        'nb_of_transactions',
        'potential_students',
        'amount',
        'details',
        'related_parties',
    ];

    /**
     * Returns a new Transaction instance from a bank transaction
     *
     * @param  array $bankTrx
     * @return self
     */
    static public function fromBankTransaction(array $bankTrx) : self
    {
        $trx = new self;

        $trx->uid = data_get($bankTrx, 'NtryRef');
        $trx->nb_of_transactions = data_get($bankTrx, 'NtryDtls.Btch.NbOfTxs', 1);
        $trx->amount = floatval($bankTrx['Amt']) * ($bankTrx['CdtDbtInd'] === 'CRDT' ? 1 : -1);
        $trx->type = isset($bankTrx['BkTxCd']) ? TransactionType::fromBankTrxCode($bankTrx['BkTxCd']) : TransactionType::UNKNOWN;
        $trx->dispute_type = ($t = data_get($bankTrx, 'NtryDtls.TxDtls.RtrInf.Rsn.Cd')) ? DisputeType::fromIso($t) : null;
        $trx->details = data_get($bankTrx, 'NtryDtls.TxDtls.AddtlTxInf');
        if (($t = data_get($bankTrx, 'NtryDtls.TxDtls.RltdPties.' . ($bankTrx['CdtDbtInd'] === 'DBIT' ? 'Dbtr' : 'Cdtr') . '.Nm'))) {
            $trx->related_parties = [trim(explode(' VIA ', $t)[0])];
        } elseif (str_contains($trx->details, ' VIA ') || str_contains($trx->details, ' / ')) {
            $trx->related_parties = [trim(explode(' VIA ', last(explode(' / ', $trx->details)))[0])];
        }
        $trx->created_at = Carbon::parse(($bankTrx['BookgDt'] ?? $bankTrx['ValDt'])['Dt']);
        $trx->is_queued = true;

        return $trx;
    }

    /**
     * Cleans the details attribute
     *
     * @param  string|null $value
     * @return string|null
     */
    static public function cleanDetailsAttribute(?string $value) : ?string
    {
        if (is_null($value)) {
            return null;
        }

        $value = preg_replace('@ {2,}@', ' ', $value);
        $value = str_replace('/LIB/', '/', substr($value, 5));

        preg_match('@^(?:IMP|VIR( INST)? ?(MADAME|MME\.?|MLLE\.?|MONSIEUR|MR\.?|M\.?)? ?(OU)? ?(MADAME|MME\.?|MLLE\.?|MONSIEUR|MR\.?|M\.?)? |VOTRE REMISE |PRLV SEPA )? ?(.+)@i', $value, $matches);

        $value = explode('/', last($matches));
        $value[0] = trim($value[0]);
        if (isset($value[1])) {
            $value[1] = str_replace('PREL FB ', '', trim($value[1]));
        }

        return implode(' / ', $value);
    }

    /**
     * Determine whether the transaction should be ignored:
     * - Outbound, DIRECT_DEBIT
     * - Outbound, not DISPUTE
     * - Outbound, WIRE_TRANSFER containing RBT or PRIME
     *
     * @return bool
     */
    public function shouldBeIgnored() : bool
    {
        return $this->amount < 0
            && (
                    $this->type === TransactionType::DIRECT_DEBIT
                 || $this->type !== TransactionType::DISPUTE
                 || (
                        $this->type === TransactionType::WIRE_TRANSFER
                     && (
                            str_contains($this->details, ' RBT ')
                         || str_contains($this->details, ' PRIME ')
                     )
                 )
            );
    }

    /**
     * Returns the student associated with this transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set the details attribute
     *
     * @param  string $value
     * @return void
     */
    public function setDetailsAttribute(?string $value)
    {
        $this->attributes['details'] = self::cleanDetailsAttribute($value);
    }
}
