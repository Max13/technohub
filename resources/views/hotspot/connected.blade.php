<!doctype html>
<html lang="{{ app()->currentLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('/css/hotspot.css') }}">
</head>

<body>

<!-- two other colors

<body class="lite">
<body class="dark">

-->

<div class="ie-fixMinHeight">
    <div class="main">
        <div class="wrap">
            <img class="logo" alt="logo" src="{{ asset('/img/hs-logo.svg') }}">

            <h1 class="info" style="margin-top: 3rem">{{ __('You are logged in!') }}</h1>

            <img class="logo" alt="Louis le BG" src="{{ asset('/img/hs-connected.jpeg') }}">
            {{-- <h3 class="info">{{ __('Uptime') }}: {{ $uptime }}</h3> --}}
        </div>
    </div>
</div>
</body>

</html>
