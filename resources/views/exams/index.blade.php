@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('My exams') }}</h1>

        <div class="row my-4">
            <div class="col-auto">
                <a href="{{ route('exams.create') }}" class="btn btn-primary me-2">{{ __('Create') }}</a>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            @foreach ($exams as $exam)
                <div class="col">
                    <div class="card h-100 w-100 hover:shadow">
                        <div class="card-body">
                            @if ($exam->seb_config_file)
                                <h5 class="float-end" style="cursor: help" title="{{ __('Protected by SEB') }}"><i class="bi bi-shield-lock"></i></h5>
                            @endif

                            <h5 class="card-title">
                                <a class="text-decoration-none text-reset" href="{{ route('exams.show', $exam) }}">
                                    {{ $exam->name }}
                                </a>
                            </h5>

                            <div class="d-flex justify-content-between my-3">
                                <p class="card-text mb-0">{{ $exam->questions_count }} {{ __('Questions') }}</p>
                            </div>

                            <div class="d-flex justify-content-between">
                                @if (auth()->user()->whereRelation('roles', 'name', 'Admin')->exists())
                                    <p class="card-text small text-muted mb-0">{{ $exam->author->fullname }}</p>
                                @endif

                                <p class="card-text small text-muted mb-0">{{ trans_choice(':count players', $exam->assignments_count) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
