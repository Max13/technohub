@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $exam->name }}</h1>

        <div class="row my-4">
            <div class="col-auto col-lg-8 mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">{{ __('Exam completed!') }}</h2>
                        <p class="card-text text-center">{{ __('You have completed this exam in :duration', ['duration' => $duration]) }}</p>
                        <div class="text-center mt-4">
                            <a href="{{ route('exams.assignments.index') }}" class="btn btn-lg btn-primary">{{ __('My exams') }}</a>
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
