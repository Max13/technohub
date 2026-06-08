@php use App\Models\Accounting\TransactionType; @endphp
@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <div class="row mb-3">
            <h1>{{ __('Transactions') }}</h1>
            <h4>{{ __('Queue') }} ({{ $transactions->count() }})</h4>
        </div>

        {{--<div class="row mb-2">
            <div class="col-auto">
                <a class="link-underline link-underline-opacity-0" href="#">
                    <i class="bi bi-chevron-left"></i>&nbsp;{{ __('Back') }}
                </a>
            </div>
        </div>--}}

        <div class="row g-3 my-2">
            @if ($errors->any())
                <div class="alert alert-danger mt-4 p-2 col-lg-6 col-md-8 mx-auto" role="alert">
                    @if ($errors->count() === 1)
                        <span class="visually-hidden">{{ __('Error') }}: </span>{{ $errors->first() }}
                    @else
                        <ul>
                            @foreach($errors->all() as $message)
                                <li class="text-start"><span class="visually-hidden">{{ __('Error') }}: </span>{{ $message }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if ($transactions->count())
                <div class="table-responsive">
                    <form id="transaction-queue-form" action="{{ route('accounting.transactions.queue.process') }}" method="POST">
                        @csrf
                        <table class="table table-striped">
                            <thead class="text-nowrap">
                                <tr>
                                    <th scope="col">&#x23;</th>
                                    <th scope="col">{{ __('Date') }}</th>
                                    <th scope="col">{{ __('Amount') }}</th>
                                    <th scope="col">{{ __('Type') }}</th>
                                    <th scope="col">{{ __('Related') }}</th>
                                    <th scope="col">{{ __('Reference') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr data-transaction-id="{{ $transaction->id }}">
                                        <td class="small font-monospace">{{ $transaction->id }}</td>
                                        <td class="text-nowrap">{{ $transaction->created_at->toDateString() }}</td>
                                        <td class="text-end">
                                            <span class="font-monospace">{!! number_format($transaction->amount, 2, thousands_separator: '&nbsp;') !!}</span><br>
                                        </td>
                                        <td>
                                            <span @class([
                                                'badge',
                                                'text-bg-secondary' => $transaction->type !== App\Models\Accounting\TransactionType::DISPUTE,
                                                'text-bg-warning' => $transaction->type === App\Models\Accounting\TransactionType::DISPUTE,
                                            ]) @if ($transaction->type === App\Models\Accounting\TransactionType::DISPUTE && $transaction->dispute_type !== null && $transaction->dispute_type !== App\Models\Accounting\DisputeType::UNKNOWN) data-bs-toggle="tooltip" title="{{ $transaction->dispute_type->title() }}" @endif>
                                                {{ $transaction->type->value }}
                                            </span>
                                            @if ($transaction->type === App\Models\Accounting\TransactionType::DISPUTE && $transaction->dispute_type !== null && $transaction->dispute_type !== App\Models\Accounting\DisputeType::UNKNOWN)
                                                <p class="fst-italic small my-2">
                                                    {{ $transaction->dispute_type->title() }}
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($transaction->user)
                                                <a href="{{ route('users.show', $transaction->user['id']) }}" target="_blank">
                                                    {{ $transaction->user['fullname'] }}
                                                </a>&nbsp;&check;
                                            @else
                                                @if (($c = count($potStudents = $transaction->potential_students ?? [])) > 0)
                                                    <div class="mb-3">
                                                        @foreach ($potStudents as $studentId)
                                                            <div class="form-check" data-radio-user-id>
                                                                <input class="form-check-input" type="radio" id="transaction[{{ $transaction->id }}][suggestions][{{ $studentId }}]" value="{{ $studentId }}" @if (old("transaction.$transaction->id.user_id") == $studentId) checked @endif>
                                                                <label class="form-check-label text-nowrap" for="transaction[{{ $transaction->id }}][suggestions][{{ $studentId }}]">
                                                                    {{ $students[$studentId]['fullname'] }}
                                                                    @if ($students[$studentId]['is_active'])
                                                                        @if ($students[$studentId]['classroom'])
                                                                            <small>&ndash; {{ $students[$studentId]['classroom'] }}</small>
                                                                       @endif
                                                                    @else
                                                                        <small>&nbsp;<i class="bi bi-trash fw-bolder"></i></small>
                                                                    @endif
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif (count($transaction->related_parties ?? []))
                                                    <div class="mb-3">
                                                        @foreach ($transaction->related_parties as $name)
                                                            {{ $name }}&nbsp;(?)<br>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <div data-autocomplete>
                                                    <label class="visually-hidden" for="transaction[{{ $transaction->id }}][autocomplete]">{{ __('Student\'s name') }}</label>
                                                    <input type="text" class="form-control" id="transaction[{{ $transaction->id }}][autocomplete]" placeholder="{{ __('Student\'s name') }}" aria-label="{{ __('Student\'s name') }}" autocomplete="off" @if (old("transaction.$transaction->id.user_id")) value="{{ $students[old("transaction.$transaction->id.user_id")]['fullname'] }}" @endif>
                                                    <input type="hidden" name="transaction[{{ $transaction->id }}][id]" value="{{ $transaction->id }}">
                                                    <input type="hidden" id="transaction[{{ $transaction->id }}][user_id]" @if (old("transaction.$transaction->id.user_id")) name="transaction[{{ $transaction->id }}][user_id]" value="{{ old("transaction.$transaction->id.user_id") }}" @endif>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($transaction->type === App\Models\Accounting\TransactionType::DISPUTE && !empty($transaction->related_parties))
                                                <p>{{ implode('<br>', $transaction->related_parties) }}</p>
                                            @endif
                                            <i class="small">{{ $transaction->details }}</i>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="{{ __('Actions') }}" data-actions>
                                                @if ($transaction->user_id)
                                                <input type="radio" class="btn-check" id="transaction[{{ $transaction->id }}][approve]" name="transaction[{{ $transaction->id }}][action]" value="approve" autocomplete="off" @if (old("transaction.$transaction->id.action") === 'approve') checked @endif>
                                                <label class="btn btn-outline-success position-relative" for="transaction[{{ $transaction->id }}][approve]" title="{{ __('Approve this transaction') }}" aria-label="{{ __('Approve this transaction') }}">
                                                    <i class="bi bi-inbox"></i>
                                                    <i class="bi bi-arrow-down-short position-absolute start-50 translate-middle-x text-primary" style="font-size: 1.5rem; top: -20% !important;"></i>
                                                </label>
                                                @endif

                                                <input type="{{ $transaction->user_id ? 'radio' : 'checkbox' }}" class="btn-check" id="transaction[{{ $transaction->id }}][reject]" name="transaction[{{ $transaction->id }}][action]" value="reject" autocomplete="off" @if (old("transaction.$transaction->id.action") === 'reject') checked @endif>
                                                <label class="btn btn-outline-danger" for="transaction[{{ $transaction->id }}][reject]" title="{{ __('Delete this transaction') }}" aria-label="{{ __('Delete this transaction') }}">
                                                    <i class="bi bi-trash"></i>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <button type="submit" class="btn btn-primary col-6 d-block mx-auto">{{ __('Submit') }}</button>
                    </form>
                </div>
            @else
                <h2 class="text-center">
                    {{ __('Nothing in queue') }}
                    <i class="bi bi-hand-thumbs-up text-success ms-2"></i>
                </h2>
            @endif
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        const studentsList = {!! $students->toJson() !!};
        const clearTransactionSuggestions = rootEl => {
            rootEl.querySelectorAll('[data-radio-user-id] input[id^="transaction[' + rootEl.dataset.transactionId + '][suggestions]"]:checked')
                  .forEach(el => {
                      el.checked = false;
                  });
        };
        const clearAutocomplete = rootEl => {
            rootEl.querySelector('[data-autocomplete] input[id="transaction[' + rootEl.dataset.transactionId + '][autocomplete]"]')
                  .value = '';

            const userIdField = rootEl.querySelector('[data-autocomplete] input[id="transaction[' + rootEl.dataset.transactionId + '][user_id]"]');
            userIdField.name = '';
            userIdField.value = '';
        };
        const clearActions = rootEl => {
            rootEl.querySelectorAll('input[name="transaction[' + rootEl.dataset.transactionId + '][action]"]:checked')
                  .forEach(el => {
                      el.checked = false;
                  });
        }

        document.querySelectorAll('tr[data-transaction-id]')
                .forEach(trEl => {
                    trEl.querySelectorAll('[data-radio-user-id]')
                        .forEach(el => {
                            el.addEventListener('click', ev => {
                                clearAutocomplete(trEl);

                                const userIdField = trEl.querySelector('[data-autocomplete] input[id="transaction[' + trEl.dataset.transactionId + '][user_id]"]');
                                userIdField.name = userIdField.id;
                                userIdField.value = ev.target.value;

                                clearActions(trEl);
                            });
                        });

                    trEl.querySelectorAll('[data-autocomplete] input[id="transaction[' + trEl.dataset.transactionId + '][autocomplete]"]')
                        .forEach(el => {
                            new Autocomplete(el, {
                                data: studentsList,
                                label: 'fullname',
                                value: 'id',
                                maximumItems: 5,
                                onSelectItem: ({label, value}) => {
                                    const userIdField = trEl.querySelector('[data-autocomplete] input[id="transaction[' + trEl.dataset.transactionId + '][user_id]"]');

                                    userIdField.name = userIdField.id;
                                    userIdField.value = value;
                                }
                            });

                            el.addEventListener('focus', ev => {
                                clearTransactionSuggestions(trEl);
                                clearActions(trEl);
                            });
                        });

                    trEl.querySelectorAll('[data-actions] > input[type="radio"], [data-actions] > input[type="checkbox"]')
                        .forEach(el => {
                            el.addEventListener('click', ev => {
                                clearTransactionSuggestions(trEl);
                                clearAutocomplete(trEl);
                            });
                        });
                });
    </script>
@endpush
