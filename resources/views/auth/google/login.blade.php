<!doctype html>
<html lang="{{ app()->currentLocale() }}" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.118.2">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ mix('/img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ mix('/img/favicons/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ mix('/img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ mix('/img/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ mix('/img/favicons/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ mix('/img/favicons/safari-pinned-tab.svg') }}" color="#000000">
    <link rel="shortcut icon" href="{{ mix('img/favicons/favicon.ico') }}">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="msapplication-config" content="{{ mix('img/favicons/browserconfig.xml') }}">
    <meta name="theme-color" content="#ffffff">

    <script>
        (() => {
            if (document.documentElement.getAttribute('data-bs-theme') === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
</head>
<body class="d-flex align-items-center py-4 bg-body-tertiary">
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

    <script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>
