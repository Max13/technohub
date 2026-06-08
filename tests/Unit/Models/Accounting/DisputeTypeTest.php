<?php

namespace Unit\Models\Accounting;

use App\Models\Accounting\DisputeType;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DisputeTypeTest extends TestCase
{
    use WithFaker;

    public function provider(): array
    {
        return [
            'AC01' => DisputeType::INCORRECT_ACCOUNT,
            'AC04' => DisputeType::CLOSED_ACCOUNT,
            'AC06' => DisputeType::BLOCKED_ACCOUNT,
            'AG01' => DisputeType::TRANSACTION_FORBIDDEN,
            'AG02' => DisputeType::INVALID_OPERATION_CODE,
            'AM04' => DisputeType::INSUFFICIENT_FUNDS,
            'AM05' => DisputeType::DUPLICATION,
            'ED05' => DisputeType::SETTLEMENT_FAILED,
            'MD01' => DisputeType::NOT_AUTHORIZED,
            'MD02' => DisputeType::INVALID_MANDATE,
            'MD06' => DisputeType::DISPUTED_TRANSACTION,
            'MD07' => DisputeType::DECEASED,
            'MS02' => DisputeType::DISPUTED_MANDATE,
            'MS03' => DisputeType::REASON_NOT_PROVIDED,
            'RC01' => DisputeType::BANK_ID_INCORRECT,
            'SL01' => DisputeType::BANK_SERVICE,
        ];
    }

    public function test_instanciate_fromIso()
    {
        foreach ($this->provider() as $iso => $type) {
            $this->assertEquals($type, DisputeType::fromIso($iso));
        }

        $this->assertEquals(DisputeType::UNKNOWN, DisputeType::fromIso($this->faker->word));
    }
}
