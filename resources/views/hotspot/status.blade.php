@extends('layouts.app')

@push('styles')
    <style>
        main {
            padding: 6em 3em;
        }

        .alert {
            margin-top: 5em;
        }

        .table {
            --bs-table-bg: transparent;
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')
    <main class="mx-auto col-4">
        <img class="d-block dark:d-none" src="{{ asset('/img/logo-h_black.svg') }}" alt="ITIC Logo">
        <img class="d-block light:d-none" src="{{ asset('/img/logo-h_white.svg') }}" alt="ITIC Logo">

        <div class="alert alert-light p-2" role="alert">
            <table class="table table-borderless table-sm">
                <tr>
                    <td>{{ __('MAC Address') }}</td>
                    <td class="font-monospace">{{ $mac }}</td>
                </tr>
                <tr>
                    <td>{{ __('IP Address') }}</td>
                    <td class="font-monospace">{{ $ip }}</td>
                </tr>
                <tr>
                    <td>{{ __('Since') }}</td>
                    <td>{{ $uptime }}</td>
                </tr>
                <tr>
                    <td>{{ __('Traffic') }} <small>(&darr; / &uarr;)</small></td>
                    <td>{{ $bytes_in }} / {{ $bytes_out }}</td>
                </tr>
            </table>
        </div>
    </main>
@endsection
