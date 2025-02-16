@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1 class="mb-4">{{ __('My exams') }}</h1>

        <div class="row mb-4">
            <div class="col-md-10 mx-auto">
                <table class="table caption-top">
                    <caption>{{ __('Ongoing exams') }}</caption>
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Assigned at') }}</th>
                            <th scope="col">{{ __('Valid until') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ongoing as $assignment)
                            <tr>
                                <th scope="row">
                                    {{ $assignment->exam->name }}
                                    @if ($assignment->exam->seb_config_key)
                                        <i class="bi bi-shield-lock ms-1" title="{{ __('Protected by SEB') }}"></i>
                                    @endif
                                </th>
                                <td>
                                    {{ $assignment->created_at->toDateString() }}<br>
                                    <small class="text-secondary">{{ __('By') }} {{ $assignment->exam->author->fullname }}</small>
                                </td>
                                <td>{{ $assignment->valid_until ? $assignment->valid_until->toDateString() : '∞' }}</td>
                                <td>
                                    @if ($assignment->is_started)
                                        <span class="fs-3 text-warning" title="{{ __('Started') }}" aria-label="{{ __('Started') }}">
                                            <i class="bi bi-clock-history" aria-hidden="true"></i>
                                        </span>
                                    @else
                                        {{ __('To do') }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('exams.assignments.start', $assignment) }}" class="btn btn-primary" title="{{ __('Continue') }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 mx-auto">
                <table class="table caption-top">
                    <caption>{{ __('Completed exams') }}</caption>
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Assigned at') }}</th>
                            <th scope="col">{{ __('Valid until') }}</th>
                            <th scope="col">{{ __('Completed at') }}</th>
                            <th scope="col">{{ __('Points') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($completed as $assignment)
                            <tr>
                                <th scope="row">
                                    {{ $assignment->exam->name }}
                                    @if ($assignment->exam->seb_config_key)
                                        <i class="bi bi-shield-lock ms-1" title="{{ __('Protected by SEB') }}"></i>
                                    @endif
                                </th>
                                <td>
                                    {{ $assignment->created_at->toDateString() }}<br>
                                    <small class="text-secondary">{{ __('By') }} {{ $assignment->exam->author->fullname }}</small>
                                </td>
                                <td>{{ $assignment->valid_until ? $assignment->valid_until->toDateString() : '∞' }}</td>
                                <td>
                                    {{ $assignment->ended_at ? $assignment->ended_at->toDateString() : '–' }}<br>
                                    <small class="text-secondary">{{ Carbon\CarbonInterval::createFromFormat('s', $assignment->duration)->cascade()->forHumans(['short' => true]) }}</small>
                                </td>
                                <td class="font-monospace">{{ str_replace('%', ' ', sprintf('%\'%5.2f', $assignment->points)) }}<small class="text-secondary">/20</small></td>
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
