@extends('layouts.app')

@section('bodyClass', 'd-flex align-items-center')
@section('bsTheme', 'dark')

@section('content')
    <x-matrix-rain/>
    <main class="m-auto w-100" style="max-width:700px">
        <img class="d-block light:d-none mt-4 mb-5 w-50 mx-auto" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo">
        <p class="text-center text-muted matrix-font fs-5">{{ __('Follow the white rabbit') }}</p>

        <div class="col-md-4 offset-md-4">

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

            <form class="row g-3" action="{{ route('auth.itic.doLogin') }}" method="post">
                @csrf

                <div class="col-12">
                    <label class="visually-hidden" for="username">{{ __('Username') }}</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="{{ __('Username') }}">
                </div>

                <div class="col-12">
                    <label class="visually-hidden" for="password">{{ __('Username') }}</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="{{ __('Password') }}">
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
                    </div>
                </div>

                <style>
                    .btn:hover {
                        /*box-shadow: 0 1rem 3rem rgba(0, 255, 64, .175);*/
                        box-shadow: 0 0 20px 10px #00ff40;
                        /*transform: scale(1.04);*/
                    }
                </style>
                <div class="col-12">
                    <button type="submit" class="btn w-100" style="--bs-btn-bg: transparent;--bs-btn-hover-bg: transparent;--bs-btn-hover-border-color: #00ff40;--bs-btn-border-color: #00ff40;">{{ __('Login') }}</button>
                </div>
            </form>

        </div>

        <p class="mt-4 text-center text-muted small">{{ __('If you need help, contact the IT!') }}</p>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
