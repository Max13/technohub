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
        <div class="wrap animated fadeIn">
            <img class="logo" alt="logo" src="{{ asset('/img/hs-logo.svg') }}">

            <form action="{{ route('hotspot.ypareo.doLogin') }}" method="post">
                <input type="hidden" name="captive" value="{{ $captive }}" />
                <input type="hidden" name="dst" value="{{ $dst }}" />
                <input type="hidden" name="mac" value="{{ $mac }}" />
                <input type="hidden" name="popup" value="false" />


                <p class="info">{!! __('You need to be logged-in to access this network. Use your <u>Ypareo</u> credentials to continue.') !!}</p>

                @if ($errors->any())
                <p class="info alert">
                    {{ $errors->first() }}
                </p>
                @endif

                <label>
                    <img class="ico" src="{{ asset('/img/icon-user.svg') }}" alt="#" />
                    <input name="username" type="text" value="{{ old('username') }}" placeholder="{{ __('Username') }}" />
                </label>

                <label>
                    <img class="ico" src="{{ asset('/img/icon-password.svg') }}" alt="#" />
                    <input name="password" type="password" placeholder="{{ __('Password') }}" />
                </label>

                <input type="submit" value="{{ __('Connect') }}" />

            </form>

            <p class="info bt">{{ __('If you need help, come see us in room 14!') }}</p>

            <pre>HS: {{ $hs }}<br>Captive: {{ $captive }}<br>Dst: {{ $dst }}<br>Mac: {{ $mac }}</pre>

        </div>
    </div>
</div>
</body>

</html>
