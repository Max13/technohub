<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Common
            $table->string('origin');
            $table->string('type');
            $table->decimal('amount');
            $table->string('label');
            $table->string('details')->nullable();
            $table->foreignIdFor(User::class, 'staff_id')
                  ->constrained((new User)->getTable())
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->foreignIdFor(User::class, 'student_id')
                  ->nullable()
                  ->constrained((new User)->getTable())
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->json('potential_students')->nullable();

            // Bank-specific
            $table->string('bank_uid')->nullable()->unique();
            $table->boolean('is_queued')->default(true);
            $table->tinyInteger('nb_of_transactions')->unsigned()->default(1);
            $table->string('dispute_type')->nullable();
            $table->json('related_parties')->nullable();

            // Accounting-specific
            $table->string('student_status')->nullable();
            $table->year('year')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
