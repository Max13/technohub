@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Dashboard') }}</h1>

        <div class="row my-5 mx-auto" style="max-width:20rem">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $user->fullname }}</h5>
                    <h6 class="card-subtitle mb-4 text-body-secondary">{{ $user->currentTraining->name }}</h6>
                    <p class="card-text">
                        <span class="lead">{{ $points }} {{ __('Points') }}</span>
                        @if ($points <= 0)
                            <span class="ms-1">⚠️</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
