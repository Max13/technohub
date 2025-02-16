@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $exam->name }}</h1>

        <div class="row my-4">
            <div class="col-auto col-lg-8 mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">{{ __('Question expired') }}</h2>
                        <p class="card-text">{{ __('This question has expired. Either you have already answered it, took too long to answer, or tried to refresh it.') }}</p>
                        <p class="card-text">{{ __('Either way, just go to the next question') }}.</p>
                        <div class="text-center mt-4">
                            <a href="{{ $next }}" class="btn btn-lg btn-primary">{{ __('Next question') }} <i class="bi bi-arrow-right-circle ms-2"></i></a>
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
