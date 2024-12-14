<?php

use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Training::class)->constrained()->cascadeOnDelete();
            $table->integer('ypareo_id')->unsigned();
            $table->string('label');
            $table->boolean('is_delay');
            $table->boolean('is_justified');
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->mediumInteger('duration')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absences');
    }
}
