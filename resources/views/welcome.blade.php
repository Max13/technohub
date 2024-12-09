@extends('layouts.app')

@push('styles')
    <style>
        nav {
            max-width: 700px;
        }

        main img {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 500px;
        }
    </style>
@endpush

@section('content')
    <nav class="navbar navbar-expand mx-auto">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="{{ route('auth.itic.showLogin') }}">{{ __('Login') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <img class="dark:d-none" src="{{ mix('/img/logo-h_black.svg') }}" alt="ITIC Logo">
        <img class="light:d-none" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo">
    </main>
@endsection
