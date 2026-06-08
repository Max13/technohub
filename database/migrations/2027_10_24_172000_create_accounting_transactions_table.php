<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'student_id')->nullable()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('student_firstname')->nullable();
            $table->string('student_lastname')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('type');
            $table->decimal('amount');
            $table->string('label');
            $table->string('student_status')->nullable();
            $table->string('rejection_status')->nullable();
            $table->string('note')->nullable();
            $table->year('year');
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
        Schema::dropIfExists('accounting_transactions');
    }
}
