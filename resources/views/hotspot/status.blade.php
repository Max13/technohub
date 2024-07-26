@extends('layouts.app')

@push('styles')
    <style>
        main {
            padding: 6em 3em;
        }

        .alert {
            margin-top: 5em;
        }
    </style>
@endpush

@section('content')
    <main>
        <img class="d-block dark:d-none mx-auto" src="{{ mix('/img/logo-h_black.svg') }}" alt="ITIC Logo">
        <img class="d-block light:d-none mx-auto" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo">

        <div class="alert alert-dark p-2" role="alert">
            <ul>
                <li>{{ __('MAC Address') }}: {{ $mac }}</li>
                <li>{{ __('IP Address') }}: {{ $ip }}</li>
                <li>{{ __('Since') }}: {{ $uptime }}</li>
                <li>{{ __('Traffic') }}: {{ $bytes_in }} / {{ $bytes_out }}</li>
            </ul>
        </div>
    </main>
@endsection
