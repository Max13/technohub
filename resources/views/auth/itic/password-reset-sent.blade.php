@extends('layouts.app')

@push('styles')
    <style>
        nav {
            max-width: 700px;
        }

        main > * {
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
        <div class="text-center">
            <h3 class="lead mb-4">{{ __('If the email address provided matches an account, you will receive a password reset link') }}</h3>
            <h4 class="lead">{{ __('You can close this page') }}</h4>
        </div>
    </main>
@endsection
