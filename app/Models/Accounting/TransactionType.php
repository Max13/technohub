<?php

namespace App\Models\Accounting;

enum TransactionType : string
{
    case CASH = 'Cash';
    case CHECK = 'Check';
    case CREDIT_CARD = 'CreditCard';
    case DIRECT_DEBIT = 'DirectDebit';
    case DISPUTE = 'Dispute';
    case WIRE_TRANSFER = 'WireTransfer';
    case UNKNOWN = 'Unknown';

    /**
     * Returns the transaction type from the bank transaction code
     *
     * @param  array $bankTrx
     * @return self
     */
    static public function fromBankTrxCode(array $bankTrx) : self
    {
        if ($bankTrx['Domn']['Cd'] !== 'PMNT') {
            return self::UNKNOWN;
        } elseif (strtoupper($bankTrx['Domn']['Fmly']['SubFmlyCd']) === 'CPDT') {
            return self::CASH;
        } elseif (in_array(strtoupper($bankTrx['Domn']['Fmly']['Cd']), ['ICHQ', 'RCHQ'], true)) {
            return self::CHECK;
        } elseif (in_array(strtoupper($bankTrx['Domn']['Fmly']['Cd']), ['CCRD', 'MCRD'], true)) {
            return self::CREDIT_CARD;
        } elseif (strtoupper($bankTrx['Domn']['Fmly']['SubFmlyCd']) === 'UPDD') {
            return self::DISPUTE;
        } elseif (in_array(strtoupper($bankTrx['Domn']['Fmly']['Cd']), ['IDDT', 'RDDT'], true)) {
            return self::DIRECT_DEBIT;
        } elseif (
            in_array(strtoupper($bankTrx['Domn']['Fmly']['Cd']), [
                'ICCN',
                'ICDT',
                'IRCT',
                'RCCN',
                'RCDT',
                'RRCT',
            ], true)
        ) {
            return self::WIRE_TRANSFER;
        }

        return self::UNKNOWN;
    }
}
