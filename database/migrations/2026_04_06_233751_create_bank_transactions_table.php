<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->boolean('is_queued')->default(true);
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->tinyInteger('nb_of_transactions')->unsigned();
            $table->json('potential_students')->nullable();
            $table->decimal('amount');
            $table->string('type');
            $table->string('dispute_type')->nullable();
            $table->string('details');
            $table->json('related_parties')->nullable();
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
        Schema::dropIfExists('bank_transactions');
    }
}
