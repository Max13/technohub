@extends('layouts.app')

@push('styles')
    <style>
        main {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 400px;
            font-size: .8rem;
        }

        img {
            max-width: 300px;
        }
    </style>
@endpush

@section('content')
    <main>
        <img class="d-block dark:d-none mx-auto" src="{{ mix('/img/logo-h_black.svg') }}" alt="ITIC Logo">
        <img class="d-block light:d-none mx-auto" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo">

        <div class="alert alert-danger mt-5 p-2" role="alert">
            <span class="visually-hidden">{{ __('Error') }}: </span>{{ __('An error occured while showing the captive portal, please contact the IT in room 14') }}
        </div>
    </main>
@endsection
