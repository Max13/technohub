@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <div class="row mb-3">
            <h1>{{ __('Transactions') }}</h1>
        </div>

        <div class="row mb-2">
            <div class="col-auto">
                <a class="link-underline link-underline-opacity-0" href="{{ route('accounting.dashboard', session('accounting.dashboard.query')) }}">
                    <i class="bi bi-chevron-left"></i>&nbsp;{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row g-3 my-2">
            <div class="col">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">&#x23;</th>
                            <th scope="col">{{ __('Origin') }}</th>
                            <th scope="col">{{ __('Date') }}&nbsp;<i class="bi bi-caret-down-fill"></i></th>
                            <th scope="col">{{ __('Amount') }}</th>
                            <th scope="col">{{ __('Related') }}</th>
                            <th scope="col">{{ __('Reference') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr @class([
                                'table-warning' => $transaction->dispute_type === App\Models\Accounting\TransactionType::DISPUTE,
                                'table-info' => $transaction->created_at->isFuture(),
                            ])>
                                <td class="small">{{ $transaction->id }}</td>
                                <td class="small">{{ __($transaction->origin->value) }}</td>
                                <td class="text-nowrap">{{ $transaction->created_at->toDateString() }}</td>
                                <td class="text-end">
                                    <span class="font-monospace">{!! number_format($transaction->amount, 2, thousands_separator: '&nbsp;') !!}</span><br>
                                    <span @class([
                                        'badge',
                                        'text-bg-secondary' => $transaction->type !== App\Models\Accounting\TransactionType::DISPUTE,
                                        'text-bg-warning' => $transaction->type === App\Models\Accounting\TransactionType::DISPUTE,
                                    ]) @if ($transaction->type === App\Models\Accounting\TransactionType::DISPUTE && $transaction->dispute_type !== null && $transaction->dispute_type !== App\Models\Accounting\DisputeType::UNKNOWN) data-bs-toggle="tooltip" title="{{ $transaction->dispute_type->title() }}" @endif>
                                        {{ __($transaction->type->value) }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    @if ($transaction->student)
                                        <a href="{{ route('users.show', $transaction->student['id']) }}" target="_blank">
                                            {{ $transaction->student['fullname'] }}
                                        </a>
                                        @if (count($transaction->student['classrooms'] ?? []) > 1)
                                            &ndash; <small>{{ array_values($transaction->student['classrooms'])[0] }}</small>
                                        @endif
                                        @if ($transaction->student['is_active'])
                                            <small><i class="bi bi-check-lg"></i></small>
                                        @else
                                            <small><i class="bi bi-trash"></i></small>
                                        @endif
                                    @else
                                        <p>{{ implode('', $transaction->normalized_related_parties) }}</p>
                                    @endif
                                </td>
                                <td>
                                    <p class="mb-2">{{ $transaction->label }}</p>
                                    <p class="fst-italic mb-0">{{ $transaction->details }}</p>
                                </td>
                                {{--<td class="align-middle">
                                    <div class="btn-group" role="group" aria-label="{{ __('Actions') }}">
                                        @if($transaction->rejection_status === App\Models\Accounting\TransactionStatus::MISSED)
                                            <a href="--}}{{-- route('users.accounting.pay', $transaction) --}}{{--" class="btn btn-outline-primary" title="{{ __('Regularize this arrear') }}" aria-label="{{ __('Regularize this arrear') }}">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-primary" title="{{ __('Schedule this arrear') }}" aria-label="{{ __('Schedule this arrear') }}">
                                                <i class="bi bi-clock"></i>
                                            </button>
                                            <a href="--}}{{-- route('users.accounting.ignore', $transaction) --}}{{--" class="btn btn-outline-primary" title="{{ __('Ignore this arrear') }}" aria-label="{{ __('Ignore this arrear') }}">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>--}}
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
