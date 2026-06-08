@php use App\Models\Accounting\TransactionStatus; @endphp
@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Accounting') }}</h1>

        <div class="row my-3">
            <div class="col-auto">
                <input type="text" class="form-control d-block" id="name" name="name" data-filter-name data-location="{{ route('users.accounting.index', '%id%') }}" placeholder="{{ __('Student\'s name') }}" aria-label="{{ __('Student\'s name') }}" autocomplete="off" required>
            </div>
        </div>

        <div class="row my-3">
            <form id="filter">
                <div class="col-auto">
                    <div class="btn-group" role="group" aria-label="{{ __('Filter') }}" data-filter-status>
                        <input type="radio" class="btn-check" name="status" id="status-all" value="" checked autocomplete="off">
                        <label class="btn btn-outline-primary" for="status-all">{{ __('All') }}</label>

                        <input type="radio" class="btn-check" name="status" id="status-arrears" value="arrears" @if(request()->query('status') === 'arrears') checked @endif autocomplete="off">
                        <label class="btn btn-outline-primary" for="status-arrears">{{ __('Arrears') }}</label>

                        <input type="radio" class="btn-check" name="status" id="status-init" value="init" @if(request()->query('status') === 'init') checked @endif autocomplete="off">
                        <label class="btn btn-outline-primary" for="status-init">{{ __('Full-time') }}</label>

                        <input type="radio" class="btn-check" name="status" id="status-alt" value="alt" @if(request()->query('status') === 'alt') checked @endif autocomplete="off">
                        <label class="btn btn-outline-primary" for="status-alt">{{ __('Work-study') }}</label>
                    </div>
                </div>
            </form>
        </div>

        <div class="row g-3 my-2">
            <div class="col-md-10 mx-auto">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="align-top">
                            <th scope="col">#</th>
                            <th scope="col">{{ __('Lastname') }}</th>
                            <th scope="col">{{ __('Firstname') }}</th>
                            <th scope="col">{{ __('Last classroom') }}</th>
                            <th scope="col">
                                {{ __('Account') }}<br>
                                <small class="text-secondary fw-lighter">({{ __('Upcoming') }})</small>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr data-href="{{ route('users.accounting.index', $student) }}" style="cursor:pointer">
                                <td>{{ $student->id }}</td>
                                <td>
                                    @if($student->lastTransaction->rejection_status === App\Models\Accounting\TransactionStatus::MISSED)
                                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>&nbsp;
                                    @endif
                                    {{ $student->lastname }}
                                </td>
                                <td>{{ $student->firstname }}</td>
                                <td>
                                    @php $currentClassroom = $student->classrooms->first() @endphp
                                    {!! $currentClassroom?->shortname ?? '<span class="text-secondary">' . __('Unknown training') . '</span>' !!}
                                    @if($currentClassroom)
                                        <br><small class="text-secondary">{{ $currentClassroom->pivot->year }}&ndash;{{ $currentClassroom->pivot->year + 1 }}</small>
                                    @endif
                                </td>
                                <td class="text-end font-monospace">
                                    {!! number_format($student->past_transactions_sum_amount, 2, thousands_separator: '&nbsp;') !!}<br>
                                    <small class="text-secondary fw-lighter">
                                        {!! number_format($student->future_transactions_sum_amount, 2, thousands_separator: '&nbsp;') !!}<br>
                                    </small>
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
    <script>
        (() => {
            const ac = new Autocomplete(document.querySelector('[data-filter-name]'), {
                data: {!! $studentsJson !!},
                label: 'fullname',
                value: 'id',
                maximumItems: 5,
                onSelectItem: ({label, value}) => {
                    window.location.href = ac.field.dataset.location.replace('%id%', value);
                }
            });

            const filterForm = document.getElementById('filter');
            const statusFilter = filterForm.querySelector('[data-filter-status]');
            statusFilter.addEventListener('change', () => {
                filterForm.submit();
                statusFilter.querySelectorAll('input').forEach(input => {
                    input.disabled = true;
                });
            });

            document.querySelectorAll('[data-href]').forEach(el => {
                el.addEventListener('click', () => {
                    window.location.href = el.dataset.href;
                });
            })
        })();
    </script>
@endpush
