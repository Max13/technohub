<?php

namespace Database\Seeders\Marking;

use App\Models\Marking\Criterion;
use App\Models\Marking\Point;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class PointsSeeder extends Seeder
{
    /**
     * Seed the application's Marking Criteria.
     *
     * @return void
     */
    public function run()
    {
        User::where('is_student', true)->each(function ($user) {
            Point::factory(5)
                 ->state(new Sequence(
                     function(Sequence $sequence) {
                         return ['criterion_id' => Criterion::all()->random()->id];
                     },
                 ))
                 ->state(new Sequence(
                     function(Sequence $sequence) {
                         return ['staff_id' => User::where('is_student', false)->get()->random()->id];
                     },
                 ))
                 ->for($user, 'student')
                 ->create();
        });
    }
}
