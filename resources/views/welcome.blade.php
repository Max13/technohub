@extends('layouts.app')

@push('styles')
    <style>
        img {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 500px;
        }
    </style>
@endpush

@section('content')
    <main>
        <img class="dark:d-none" src="{{ mix('/img/logo-h_black.svg') }}" alt="ITIC Logo">
        <img class="light:d-none" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo">
    </main>
@endsection
