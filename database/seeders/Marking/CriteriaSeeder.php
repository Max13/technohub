<?php

namespace Database\Seeders\Marking;

use App\Models\Marking\Criterion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class CriteriaSeeder extends Seeder
{
    /**
     * Seed the application's Marking Criteria.
     *
     * @return void
     */
    public function run()
    {
        Criterion::factory(20)
                 ->state(new Sequence(
                     function(Sequence $sequence) {
                          return ['user_id' => User::where('is_staff', true)->get()->random()];
                      },
                 ))
                 ->create();
    }
}
