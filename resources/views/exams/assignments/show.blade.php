@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $exam->name }}</h1>

        {{-- Rules --}}
        <div class="row my-4">
            <div class="col-auto col-lg-8 mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-2">{{ __('Rules') }}</h2>
                        @if ($exam->seb_config_key)
                            <h4 class="text-center mb-4"><i class="bi bi-shield-lock"></i>&nbsp;<span class="text-secondary">{!! __('Only accessible by <abbr title="Safe Exam Browser">SEB</abbr>') !!}</span></h4>
                        @endif

                        <ol class="card-text">
                            @foreach (config('exam.rules') as $rule)
                                <li class="mb-3">{{ __($rule) }}</li>
                            @endforeach
                        </ol>

                        <div class="text-center mt-4">
                            @if ($exam->seb_config_key)
                                <a class="btn btn-primary disabled" aria-disabled="true">{{ __('Start') }} <i class="bi bi-arrow-right-circle ms-2"></i></a>
                            @else
                                <a class="btn btn-primary" href="{{ route('exams.assignments.start', $answer->uuid) }}">{{ __('Start') }} <i class="bi bi-arrow-right-circle ms-2"></i></a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
