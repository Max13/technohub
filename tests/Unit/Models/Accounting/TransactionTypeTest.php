<?php

namespace Unit\Models\Accounting;

use App\Models\Accounting\TransactionType;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTypeTest extends TestCase
{
    use WithFaker;

    public function test_instanciate_from_trx_code_for_unknown_when_domain_code_is_not_pmnt()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'AAAA',
                'Fmly' => [
                    'Cd' => 'ICHQ',
                    'SubFmlyCd' => 'CHQE',
                ],
            ],
        ];

        $this->assertEquals(TransactionType::UNKNOWN, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_unknown_when_no_rule_matches()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => 'XXXX',
                    'SubFmlyCd' => 'YYYY',
                ],
            ],
        ];

        $this->assertEquals(TransactionType::UNKNOWN, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_cash()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => $this->faker->word,
                    'SubFmlyCd' => 'CPDT',
                ],
            ],
        ];

        $this->assertEquals(TransactionType::CASH, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_check_ichq()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => 'ICHQ',
                    'SubFmlyCd' => $this->faker->word,
                ],
            ],
        ];

        $this->assertEquals(TransactionType::CHECK, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_check_rchq()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => 'RCHQ',
                    'SubFmlyCd' => $this->faker->word,
                ],
            ],
        ];

        $this->assertEquals(TransactionType::CHECK, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_credit_card()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => 'MCRD',
                    'SubFmlyCd' => $this->faker->word,
                ],
            ],
        ];

        $this->assertEquals(TransactionType::CREDIT_CARD, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_disputes()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => $this->faker->word,
                    'SubFmlyCd' => 'UPDD',
                ],
            ],
        ];

        $this->assertEquals(TransactionType::DISPUTE, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_direct_debit_with_iddt()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => 'IDDT',
                    'SubFmlyCd' => $this->faker->word,
                ],
            ],
        ];

        $this->assertEquals(TransactionType::DIRECT_DEBIT, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_direct_debit_with_rddt()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => 'RDDT',
                    'SubFmlyCd' => $this->faker->word,
                ],
            ],
        ];

        $this->assertEquals(TransactionType::DIRECT_DEBIT, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_wire_transfer_with_icdt()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => 'ICDT',
                    'SubFmlyCd' => $this->faker->word,
                ],
            ],
        ];

        $this->assertEquals(TransactionType::WIRE_TRANSFER, TransactionType::fromBankTrxCode($bankTrx));
    }

    public function test_instanciate_from_trx_code_for_wire_transfer_with_rrct()
    {
        $bankTrx = [
            'Domn' => [
                'Cd' => 'PMNT',
                'Fmly' => [
                    'Cd' => 'RRCT',
                    'SubFmlyCd' => $this->faker->word,
                ],
            ],
        ];

        $this->assertEquals(TransactionType::WIRE_TRANSFER, TransactionType::fromBankTrxCode($bankTrx));
    }
}
