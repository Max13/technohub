@extends('layouts.app')

@section('bodyClass', 'd-flex align-items-center py-4 bg-body-tertiary')

@section('content')
    <main class="container-hs text-center">
        <img class="d-block dark:d-none mx-auto mt-4 w-75" src="{{ mix('/img/logo-h_black.svg') }}" alt="ITIC Logo">
        <img class="d-block light:d-none mx-auto mt-4 w-75" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo">

        <div class="mt-5">
            <p>{{ __('You need to be logged-in to access this network.') }}</p>
            <p>{!! __('By using this service, you acknowledge having read, understood and accepted the <a href="#" class="alert-link" data-bs-toggle="modal" data-bs-target="#:modalName">general terms of use</a>.', ['modalName' => 'termsModal']) !!}</p>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger mt-4 p-2" role="alert">
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

        <a class="btn login-with google w-100 my-2 @error('callback')) disabled @enderror" role="button" href="{{ route('auth.google.redirect', ['callback' => $callback, 'domains' => $domains], false) }}">{{ __('Continue with Google') }}</a>

        <p class="text-muted mt-1">{{ __('If you need help, contact the IT!') }}</p>
    </main>

    <x-hs-terms-modal id="termsModal" />
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
