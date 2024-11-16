<!doctype html>
<html lang="{{ app()->currentLocale() }}" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{!! isset($title) ? $title.' &ndash; ' : null !!}{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    @stack('styles')

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ mix('/img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ mix('/img/favicons/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ mix('/img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ mix('/img/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ mix('/img/favicons/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ mix('/img/favicons/safari-pinned-tab.svg') }}" color="#000000">
    <link rel="shortcut icon" href="{{ mix('/img/favicons/favicon.ico') }}">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="msapplication-config" content="{{ mix('/img/favicons/browserconfig.xml') }}">
    <meta name="theme-color" content="#ffffff">

    @stack('styles')

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
<body class="@yield('bodyClass', null)">
    @yield('content')

    @stack('scripts')
</body>
</html>
