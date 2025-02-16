<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_staff' => $this->faker->boolean,
            'is_student' => $this->faker->boolean,
            'is_trainer' => $this->faker->boolean,
            'ypareo_id' => $this->faker->unique()->optional()->randomNumber,
            'ypareo_login' => function (array $attributes) {
                return $attributes['ypareo_id'] ? strtoupper($attributes['lastname']) : null;
            },
            'ypareo_uuid' => function (array $attributes) {
                return $attributes['ypareo_id'] ? $this->faker->uuid : null;
            },
            'ypareo_sso' => function (array $attributes) {
                return $attributes['ypareo_uuid'];
            },
            'lastname' => $this->faker->lastName,
            'firstname' => $this->faker->firstName,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'birthdate' => $this->faker->dateTimeInInterval('-30 years', '-18 years')->setTime(0, 0),
            'last_logged_in_at' => $this->faker->dateTimeThisMonth,
        ];
    }

    /**
     * Indicate that the model is an administrator
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function admin()
    {
        return $this->hasAttached(Role::firstOrCreate(['name' => 'Admin']))
                    ->state(function () {
                        return [
                            'is_staff' => true,
                        ];
                    });
    }

    /**
     * Indicate that the model does not come from ypareo
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function localUser()
    {
        return $this->state(function (array $attributes) {
            return [
                'ypareo_id' => null,
                'ypareo_login' => null,
                'ypareo_uuid' => null,
                'ypareo_sso' => null,
            ];
        });
    }

    /**
     * Indicate that the model is a staff
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function staff()
    {
        return $this->hasAttached(Role::firstOrCreate(['name' => 'Staff']))
                    ->state(function () {
                        return [
                            'is_staff' => true,
                        ];
                    });
    }

    /**
     * Indicate that the model is a student
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function student()
    {
        return $this->hasAttached(Role::firstOrCreate(['name' => 'Student']))
                    ->state(function () {
                        return [
                            'is_student' => true,
                        ];
                    });
    }

    /**
     * Indicate that the model is a trainer
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function trainer()
    {
        return $this->hasAttached(Role::firstOrCreate(['name' => 'Trainer']))
                    ->state(function () {
                        return [
                            'is_trainer' => true,
                        ];
                    });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
