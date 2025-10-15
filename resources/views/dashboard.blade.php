@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Dashboard') }}</h1>

        <div class="row my-5 mx-auto" style="max-width:20rem">
            <div class="card hover:shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md">
                            <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                <i class="bi bi-person p-3"></i>
                            </div>
                        </div>
                        <div class="flex-1 ms-3">
                            <h5 class="font-size-16 mb-1">{{ $user->fullname }}</h5>
                            <span class="badge badge-soft-success mb-0">{{ $user->currentTraining->name }}</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-1">
                        <p class="text-muted mb-0 mt-2">
                            <i class="bi bi-award font-size-15 align-middle pe-2 text-primary"></i>
                            {{ $points }} {{ __('Points') }}
                            @if ($points <= 0)
                                <span class="ms-1">⚠️</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
