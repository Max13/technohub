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

        <img class="d-block mx-auto w-75 mt-5" alt="Louis le BG" src="{{ asset('/img/hs-connected.jpeg') }}">

        <div class="alert alert-dark mt-4 p-2" role="alert">
            @if (isset($dst))
                <i class="loading-spinner"></i>{!! __('Redirecting to :url', ['url' => $dst]) !!}
            @else
                {{ __('You can close this page') }}
            @endif
        </div>
    </main>
@endsection

@push('scripts')
    @if (isset($dst))
        <script>
            (() => {
                setTimeout(() => {
                    window.location.href = '{{ $dst }}';
                }, 2000);
            })();
        </script>
    @endif
@endpush
