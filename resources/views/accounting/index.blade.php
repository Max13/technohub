@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <div class="row mb-3">
            <h1>{{ __('Transactions') }}</h1>
            <h4>{{ $student->lastname }} {{ $student->firstname }}</h4>
        </div>

        <div class="row mb-2">
            <div class="col-auto">
                <a class="link-underline link-underline-opacity-0" href="{{ route('users.accounting.dashboard', session('accounting.dashboard.query')) }}">
                    <i class="bi bi-chevron-left"></i>&nbsp;{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row my-3">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            @if($student->classrooms->isEmpty())
                                {{ __('Unknown training') }}
                            @else
                                {{ $student->classrooms->last()->shortname }} ({{ $student->classrooms->last()->pivot->year }})
                            @endif
                        </h6>
                        <!-- <h6 class="card-subtitle mb-2 text-body-secondary">Card subtitle</h6> -->
                        <!-- <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p> -->
                        <ul>
                            @if($lastTransaction->rejection_status === App\Models\Accounting\TransactionStatus::MISSED)
                                <li class="text-danger">{!! __('<b>:amount</b> arrears', ['amount' => number_format($lastTransaction->amount, 2, thousands_separator: '&nbsp;')]) !!}</li>
                            @else
                                <li class="text-secondary">{{ __('No arrears') }}</li>
                            @endif
                            <li>{!! __(':amount as of today (excluded)', ['amount' => number_format($pastTransactions->sum('amount'), 2, thousands_separator: '&nbsp;')]) !!}</li>
                            <li>{!! __(':amount scheduled', ['amount' => number_format($futureTransactions->sum('amount'), 2, thousands_separator: '&nbsp;')]) !!}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 my-2">
            <div class="col">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{ __('Amount') }}</th>
                            <th scope="col">{{ __('Label') }}</th>
                            <th scope="col">{{ __('Student status') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Date') }}&nbsp;<i class="bi bi-caret-down-fill"></i></th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($student->transactions as $transaction)
                            <tr class="
                                @if($transaction->rejection_status === App\Models\Accounting\TransactionStatus::MISSED)
                                    table-warning
                                @elseif($transaction->created_at->isFuture())
                                    table-info
                                @endif
                            ">
                                <td>{{ $transaction->id }}</td>
                                <td class="text-end">
                                    <span class="font-monospace">{!! number_format($transaction->amount, 2, thousands_separator: '&nbsp;') !!}</span><br>
                                    <small class="text-secondary">{{ $transaction->type->value }}</small>
                                </td>
                                <td class="small">{{ $transaction->label }}</td>
                                <td>{{ $transaction->status }}</td>
                                <td>{{ $transaction->rejection_status->value }}</td>
                                <td>{{ $transaction->created_at->toDateString() }}</td>
                                <td class="align-middle">
                                    <div class="btn-group" role="group" aria-label="{{ __('Actions') }}">
                                        @if($transaction->rejection_status === App\Models\Accounting\TransactionStatus::MISSED)
                                            <a href="{{-- route('users.accounting.pay', $transaction) --}}" class="btn btn-outline-primary" title="{{ __('Regularize this arrear') }}" aria-label="{{ __('Regularize this arrear') }}">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-primary" title="{{ __('Schedule this arrear') }}" aria-label="{{ __('Schedule this arrear') }}">
                                                <i class="bi bi-clock"></i>
                                            </button>
                                            <a href="{{-- route('users.accounting.ignore', $transaction) --}}" class="btn btn-outline-primary" title="{{ __('Ignore this arrear') }}" aria-label="{{ __('Ignore this arrear') }}">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
