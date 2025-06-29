<?php

namespace Tests\Feature\Models;

use App\Models\Absence;
use App\Models\Course;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use Tests\TestCase;

class AbsenceCourseTest extends TestCase
{
    use RefreshDatabase;

    protected $period = [];
    protected $courses = [];
    protected $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $period = today()->setMonth(Carbon::SEPTEMBER)->startOfMonth();
        if (today()->month < Carbon::SEPTEMBER) {
            $period->subYear();
        }
        $this->period = [
            'codePeriode' => 1,
            'nomPeriode' => strval($period->year),
            'dateDeb' => $period->format('d/m/Y'),
            'dateFin' => $period->copy()->addYear()->subDay()->format('d/m/Y'),
        ];

        $this->courses = Course::factory()->createMany([
            ['started_at' => today()->setHour(9)],
            ['started_at' => today()->setHour(11)],
            ['started_at' => today()->setHour(14)],
            ['started_at' => today()->setHour(16)],
        ]);

        // Mock Ypareo::getCurrentPeriod()
        $this->mock(Ypareo::class, function (MockInterface $mock) {
            $mock->shouldReceive('getCurrentPeriod')
                 ->andReturn($this->period);
        });

        $this->user = User::factory()->student()->create();
        // Attach current classroom to the courses
        $this->user->currentClassroom->courses()->attach($this->courses);
        // Attach user to the courses
        $this->user->courses()->attach($this->courses);
    }

    /*
     *          Absence
     *          #######
     * ######
     * Course
     *
     * Expected: False
     */
    public function test_absence_doesnt_have_related_course_starting_before_and_ending_before()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->addDay(),
            'ended_at' => today()->addDay()->endOfDay(),
        ]);

        $this->assertEquals(0, $absence->courses()->count());
    }

    /*
     *          Absence
     *          #######
     * ######
     * Course
     *
     * Expected: False
     */
    public function test_courses_dont_have_absence_starting_after_and_ending_after()
    {
        Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->addDay(),
            'ended_at' => today()->addDay()->endOfDay(),
        ]);

        $this->assertEquals(0, $this->courses[0]->absences()->count());
        $this->assertEquals(0, $this->courses[1]->absences()->count());
        $this->assertEquals(0, $this->courses[2]->absences()->count());
        $this->assertEquals(0, $this->courses[3]->absences()->count());
    }

    /*
     * Absence
     * #######
     *          ######
     *          Course
     *
     * Expected: False
     */
    public function test_absence_doesnt_have_related_course_starting_after_and_ending_after()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->subDay(),
            'ended_at' => today()->subDay()->endOfDay(),
        ]);

        $this->assertEquals(0, $absence->courses()->count());
    }

    /*
     * Absence
     * #######
     *          ######
     *          Course
     *
     * Expected: False
     */
    public function test_courses_dont_have_absence_starting_before_and_ending_before()
    {
        Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->addDay(),
            'ended_at' => today()->addDay()->endOfDay(),
        ]);

        $this->assertEquals(0, $this->courses[0]->absences()->count());
        $this->assertEquals(0, $this->courses[1]->absences()->count());
        $this->assertEquals(0, $this->courses[2]->absences()->count());
        $this->assertEquals(0, $this->courses[3]->absences()->count());
    }

    /*
     *    Absence
     *    #######
     * ######
     * Course
     *
     * Expected: True
     */
    public function test_absence_has_related_course_starting_before_and_ending_during()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setHour(17),
            'ended_at' => today()->setHour(19),
        ]);

        $this->assertEquals(1, $absence->courses()->count());
        $this->assertTrue($absence->courses[0]->is($this->user->courses[3]));
    }

    /*
     *    Absence
     *    #######
     * ######
     * Course
     *
     * Expected: True
     */
    public function test_course_has_absence_starting_during_and_ending_after()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setHour(17),
            'ended_at' => today()->setHour(19),
        ]);

        $this->assertEquals(0, $this->courses[0]->absences()->count());
        $this->assertEquals(0, $this->courses[1]->absences()->count());
        $this->assertEquals(0, $this->courses[2]->absences()->count());
        $this->assertEquals(1, $this->courses[3]->absences()->count());
        $this->assertTrue($this->courses[3]->absences[0]->is($absence));
    }

    /*
     * Absence
     * #######
     * #######
     * Course
     *
     * Expected: True
     */
    public function test_absence_has_related_course_starting_and_ending_at_the_same_time()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setHour(9),
            'ended_at' => today()->setTime(10, 59),
        ]);

        $this->assertEquals(1, $absence->courses()->count());
        $this->assertTrue($absence->courses[0]->is($this->user->courses[0]));
    }

    /*
     * Absence
     * #######
     * #######
     * Course
     *
     * Expected: True
     */
    public function test_course_has_related_absence_starting_and_ending_at_the_same_time()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setHour(9),
            'ended_at' => today()->setTime(10, 59),
        ]);

        $this->assertEquals(1, $this->courses[0]->absences()->count());
        $this->assertTrue($this->courses[0]->absences[0]->is($absence));
        $this->assertEquals(0, $this->courses[1]->absences()->count());
        $this->assertEquals(0, $this->courses[2]->absences()->count());
        $this->assertEquals(0, $this->courses[3]->absences()->count());
    }

    /*
     * Absence
     * #######
     *    ######
     *    Course
     *
     * Expected: True
     */
    public function test_absence_has_related_course_starting_during_and_ending_after()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setHour(8),
            'ended_at' => today()->setHour(10),
        ]);

        $this->assertEquals(1, $absence->courses()->count());
        $this->assertTrue($absence->courses[0]->is($this->user->courses[0]));
    }

    /*
     * Absence
     * #######
     *    ######
     *    Course
     *
     * Expected: True
     */
    public function test_course_has_related_absence_starting_before_and_ending_during()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setHour(8),
            'ended_at' => today()->setHour(10),
        ]);

        $this->assertEquals(1, $this->courses[0]->absences()->count());
        $this->assertTrue($this->courses[0]->absences[0]->is($absence));
        $this->assertEquals(0, $this->courses[1]->absences()->count());
        $this->assertEquals(0, $this->courses[2]->absences()->count());
        $this->assertEquals(0, $this->courses[3]->absences()->count());
    }

    /*
     * Absence
     * #######
     *  ####
     * Course
     *
     * Expected: True
     */
    public function test_absence_has_related_course_starting_during_and_ending_during()
    {
        $course = Course::factory()->create([
            'started_at' => today()->addDay()->setHour(9),
            'ended_at' => today()->addDay()->setTime(17, 59),
        ]);

        $this->user->currentClassroom->courses()->attach($course);
        $this->user->courses()->attach($course);

        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->addDay(),
            'ended_at' => today()->addDay()->endOfDay(),
        ]);

        $this->assertEquals(1, $absence->courses()->count());
        $this->assertTrue($absence->courses[0]->is($course));
    }

    /*
     * Absence
     * #######
     *  ####
     * Course
     *
     * Expected: True
     */
    public function test_course_has_related_absence_starting_before_and_ending_after()
    {
        $course = Course::factory()->create([
            'started_at' => today()->addDay()->setHour(9),
            'ended_at' => today()->addDay()->setTime(17, 59),
        ]);

        $this->user->currentClassroom->courses()->attach($course);
        $this->user->courses()->attach($course);

        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->addDay(),
            'ended_at' => today()->addDay()->endOfDay(),
        ]);

        $this->assertEquals(1, $course->absences()->count());
        $this->assertTrue($course->absences[0]->is($absence));
    }

    /*
     * Absence
     *  ####
     * ######
     * Course
     *
     * Expected: True
     */
    public function test_absence_has_related_course_starting_before_and_ending_after()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setTime(9, 30),
            'ended_at' => today()->setTime(10, 30),
        ]);

        $this->assertEquals(1, $absence->courses()->count());
        $this->assertTrue($absence->courses[0]->is($this->user->courses[0]));
    }

    /*
     * Absence
     *  ####
     * ######
     * Course
     *
     * Expected: True
     */
    public function test_course_has_related_absence_starting_during_and_ending_during()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setTime(9, 30),
            'ended_at' => today()->setTime(10, 30),
        ]);

        $this->assertEquals(1, $this->courses[0]->absences()->count());
        $this->assertTrue($this->courses[0]->absences[0]->is($absence));
        $this->assertEquals(0, $this->courses[1]->absences()->count());
        $this->assertEquals(0, $this->courses[2]->absences()->count());
        $this->assertEquals(0, $this->courses[3]->absences()->count());
    }

    // ---

    /*
     *    Absence
     *    #######
     * ###### ######
     * Course Course
     *
     * Expected: True
     */
    public function test_absence_has_related_course_starting_before_and_another_ending_after()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setHour(10),
            'ended_at' => today()->setHour(12),
        ]);

        $this->assertEquals(2, $absence->courses()->count());
        $this->assertTrue($absence->courses[0]->is($this->user->courses[0]));
        $this->assertTrue($absence->courses[1]->is($this->user->courses[1]));
    }

    /*
     *    Absence
     *    #######
     * ###### ######
     * Course Course
     *
     * Expected: True
     */
    public function test_courses_has_same_absence_starting_during_first_and_ending_during_second()
    {
        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'training_id' => $this->user->currentClassroom->training_id,
            'started_at' => today()->setHour(10),
            'ended_at' => today()->setHour(12),
        ]);

        $this->assertEquals(1, $this->courses[0]->absences()->count());
        $this->assertTrue($this->courses[0]->absences[0]->is($absence));
        $this->assertEquals(1, $this->courses[1]->absences()->count());
        $this->assertTrue($this->courses[1]->absences[0]->is($absence));
        $this->assertEquals(0, $this->courses[2]->absences()->count());
        $this->assertEquals(0, $this->courses[3]->absences()->count());
    }

    /*
     * Absence Absence
     * ####### #######
     *     ######
     *     Course
     *
     * Expected: True
     */
    public function test_absences_has_same_related_course_starting_during_first_and_ending_during_second()
    {
        $course = Course::factory()->create([
            'started_at' => today()->addDay()->setHour(11),
            'ended_at' => today()->addDay()->setTime(15, 59),
        ]);

        $this->user->currentClassroom->courses()->attach($course);
        $this->user->courses()->attach($course);

        $absences = Absence::factory()->createMany([
            [
                'user_id' => $this->user->id,
                'training_id' => $this->user->currentClassroom->training_id,
                'started_at' => today()->addDay()->setHour(9),
                'ended_at' => today()->addDay()->setHour(13),
            ],[
                'user_id' => $this->user->id,
                'training_id' => $this->user->currentClassroom->training_id,
                'started_at' => today()->addDay()->setHour(14),
                'ended_at' => today()->addDay()->setTime(17, 59),
            ]
        ]);

        $this->assertEquals(1, $absences[0]->courses()->count());
        $this->assertEquals(1, $absences[1]->courses()->count());
        $this->assertTrue($absences[0]->courses[0]->is($this->user->courses[4]));
        $this->assertTrue($absences[1]->courses[0]->is($this->user->courses[4]));
    }

    /*
     * Absence Absence
     * ####### #######
     *     ######
     *     Course
     *
     * Expected: True
     */
    public function test_course_has_two_absences_first_starting_before_ending_during_second_starting_during_ending_after()
    {
        $course = Course::factory()->create([
            'started_at' => today()->addDay()->setHour(11),
            'ended_at' => today()->addDay()->setTime(15, 59),
        ]);

        $this->user->currentClassroom->courses()->attach($course);
        $this->user->courses()->attach($course);

        $absences = Absence::factory()->createMany([
            [
                'user_id' => $this->user->id,
                'training_id' => $this->user->currentClassroom->training_id,
                'started_at' => today()->addDay()->setHour(9),
                'ended_at' => today()->addDay()->setHour(13),
            ],[
                'user_id' => $this->user->id,
                'training_id' => $this->user->currentClassroom->training_id,
                'started_at' => today()->addDay()->setHour(14),
                'ended_at' => today()->addDay()->setTime(17, 59),
            ]
        ]);

        $this->assertEquals(2, $course->absences()->count());
        $this->assertTrue($course->absences[0]->is($absences[0]));
        $this->assertTrue($course->absences[1]->is($absences[1]));
    }

    /*
     * Absence Absence
     * ####### #######
     *     ###### ######
     *     Course Course
     *
     * Expected: True
     */
    public function test_first_absence_starting_before_first_course_ending_during_and_second_absence_starting_during_first_course_ending_during_second()
    {
        $absences = Absence::factory()->createMany([
            [
                'user_id' => $this->user->id,
                'training_id' => $this->user->currentClassroom->training_id,
                'started_at' => today()->setHour(8),
                'ended_at' => today()->setTime(9, 30),
            ],[
                'user_id' => $this->user->id,
                'training_id' => $this->user->currentClassroom->training_id,
                'started_at' => today()->setHour(10),
                'ended_at' => today()->setHour(12),
            ]
        ]);

        $this->assertEquals(1, $absences[0]->courses()->count());
        $this->assertEquals(2, $absences[1]->courses()->count());
        $this->assertTrue($absences[0]->courses[0]->is($this->user->courses[0]));
        $this->assertTrue($absences[1]->courses[0]->is($this->user->courses[0]));
        $this->assertTrue($absences[1]->courses[1]->is($this->user->courses[1]));
    }

    /*
     * Absence Absence
     * ####### #######
     *     ###### ######
     *     Course Course
     *
     * Expected: True
     */
    public function test_first_course_starting_during_first_absence_ending_during_second_and_second_course_starting_during_second_absence_ending_after_second()
    {
        $absences = Absence::factory()->createMany([
            [
                'user_id' => $this->user->id,
                'training_id' => $this->user->currentClassroom->training_id,
                'started_at' => today()->setHour(8),
                'ended_at' => today()->setTime(9, 30),
            ],[
                'user_id' => $this->user->id,
                'training_id' => $this->user->currentClassroom->training_id,
                'started_at' => today()->setHour(10),
                'ended_at' => today()->setHour(12),
            ]
        ]);

        $this->assertEquals(2, $this->courses[0]->absences()->count());
        $this->assertTrue($this->courses[0]->absences[0]->is($absences[0]));
        $this->assertTrue($this->courses[0]->absences[1]->is($absences[1]));
        $this->assertEquals(1, $this->courses[1]->absences()->count());
        $this->assertTrue($this->courses[1]->absences[0]->is($absences[1]));
    }
}
