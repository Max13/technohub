<?php

namespace App\Providers;

use App\Models\Accounting\Transaction as AccountingTransaction;
use App\Models\Bank\Transaction as BankTransaction;
use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use App\Models\Marking\Criterion;
use App\Policies\Accounting\TransactionPolicy as AccoutingTransactionPolicy;
use App\Policies\Bank\TransactionPolicy as BankTransactionPolicy;
use App\Policies\Exam\AssignmentPolicy;
use App\Policies\Exam\QuestionPolicy;
use App\Policies\Marking\CriterionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AccountingTransaction::class => AccoutingTransactionPolicy::class,
        BankTransaction::class => BankTransactionPolicy::class,
        Assignment::class  => AssignmentPolicy::class,
        Criterion::class => CriterionPolicy::class,
        Question::class  => QuestionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
