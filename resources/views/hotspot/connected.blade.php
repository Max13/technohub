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
    <main class="text-center">
        <img class="d-block dark:d-none mx-auto" src="{{ mix('/img/logo-h_black.svg') }}" alt="ITIC Logo">
        <img class="d-block light:d-none mx-auto" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo">

        <div class="alert alert-info my-5 p-2" role="alert">
            {{ __('You are connected!') }}
        </div>

        <img class="d-block mx-auto w-75" alt="Louis le BG" src="{{ asset('/img/hs-connected.jpeg') }}">
    </main>
@endsection
