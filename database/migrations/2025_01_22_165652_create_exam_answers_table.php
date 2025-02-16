<?php

use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Assignment::class)->constrained('exam_assignments')->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained('exam_questions')->cascadeOnDelete();
            $table->string('status')->nullable()->default('OK');
            $table->json('value')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_answers');
    }
}
