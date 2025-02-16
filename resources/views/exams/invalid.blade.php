@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <div class="row">
            <div class="col-auto col-lg-8 mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">{{ __('Invalid exam') }}</h2>
                        <p class="card-text">{{ __('This exam code is invalid. Either you already have answered it, or it is not yet enabled, or its assignment is expired.') }}</p>
                        <div class="text-center mt-4">
                            <a href="{{ route('exams.index') }}" class="btn btn-lg btn-primary">{{ __('My exams') }}</a>
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
