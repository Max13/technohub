<?php

use App\Models\Marking\Criterion;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkingPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marking_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterion_id')->constrained('marking_criteria');
            $table->foreignId('staff_id')->constrained('users');
            $table->foreignId('student_id')->constrained('users');
            $table->integer('points');
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('marking_points');
    }
}
