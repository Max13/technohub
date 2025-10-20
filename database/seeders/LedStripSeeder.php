<?php

namespace Database\Seeders;

use App\Models\LedStrip;
use Illuminate\Database\Seeder;

class LedStripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LedStrip::factory(20)->create();
    }
}
