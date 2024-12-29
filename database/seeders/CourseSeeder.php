<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Course;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $startOfPediod = today();
        if ($startOfPediod->month < Carbon::SEPTEMBER) {
            $startOfPediod->subYear();
        }
        $startOfPediod->setMonth(Carbon::SEPTEMBER)->startOfMonth();
        $endOfPeriod = $startOfPediod->copy()->addYear()->setMonth(Carbon::AUGUST)->endOfMonth();

        $classrooms = Classroom::with('students')->get();

        CarbonPeriod::create($startOfPediod, $endOfPeriod)
                    ->filter(function (Carbon $date) {
                        return $date->isWeekday();
                    })
                    ->forEach(function (Carbon $date) use ($classrooms) {
                        Course::factory()
                              ->hasAttached($classrooms)
                              ->hasAttached($classrooms->get('students')->dd())
                              ->createMany([
                                  [
                                      'started_at' => $date->copy()->hour(9),
                                      'ended_at' => $date->copy()->hour(13),
                                      'duration' => 4 * 60,
                                  ],[
                                      'started_at' => $date->copy()->hour(14),
                                      'ended_at' => $date->copy()->hour(18),
                                      'duration' => 4 * 60,
                                  ]
                        ]);

                    });
    }
}
