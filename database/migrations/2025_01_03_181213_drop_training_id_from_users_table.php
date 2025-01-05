<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropTrainingIdFromUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::getConnection()->getConfig('driver') === 'sqlite') {
            DB::transaction(function () {
                if (!DB::unprepared('ALTER TABLE users DROP COLUMN training_id;')) {
                    throw new Exception('Could not drop training_id from users table');
                }
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('training_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('training_id')
                  ->nullable()
                  ->after('password')
                  ->constrained()
                  ->nullOnDelete();
        });
    }
}
