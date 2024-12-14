<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Database\Seeder;

class AbsenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::where('is_student', true)
            ->whereNotNull('training_id')
            ->chunkById(100, function ($students) {
                $students->each(function ($student) {
                    Absence::factory(random_int(0, 10))->create([
                        'user_id' => $student->id,
                        'training_id' => $student->training_id,
                    ]);
                });
            });
    }
}
