<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::factory()->createMany([
            [
                'is_from_ypareo' => false,
                'name' => 'Admin'
            ],[
                'is_from_ypareo' => false,
                'name' => 'HeadTeacher'
            ],[
                'is_from_ypareo' => true,
                'name' => 'Trainer'
            ],[
                'is_from_ypareo' => true,
                'name' => 'Staff'
            ],[
                'is_from_ypareo' => true,
                'name' => 'Student'
            ],[
                'is_from_ypareo' => true,
                'name' => 'Disabled',
            ],[
                'is_from_ypareo' => true,
                'name' => 'SFP',
            ],
        ])->keyBy('name');

        User::lazy(100)->each(function ($u) use ($roles) {
            $rolesToApply = [];

            if ($u->is_staff) {
                $rolesToApply[] = $roles['Staff']->id;
            }

            if ($u->is_student) {
                $rolesToApply[] = $roles['Student']->id;
            }

            if ($u->is_trainer) {
                $rolesToApply[] = $roles['Trainer']->id;
            }

            $u->roles()->sync($rolesToApply);
        });
    }
}
