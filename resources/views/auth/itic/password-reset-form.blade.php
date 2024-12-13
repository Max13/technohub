@extends('layouts.app')

@push('styles')
    <style>
        main > * {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 500px;
        }

        form {
            max-width: 300px !important;
        }
    </style>
@endpush

@section('content')
    <main>
        <div class="row mx-auto">
            <p class="lead mb-4 text-center">{{ __('You can reset your password using the form below') }}</p>

            <form method="post" class="mx-auto">
                @csrf
                @method('patch')

                @if ($errors->any())
                    <div class="alert alert-danger my-4 p-2" role="alert">
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

                <div class="row mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input type="password" id="password" name="password" class="form-control" aria-describedby="password-help">
                    <div id="password-help" class="form-text">
                        {{ __('Your password must be 8 characters long minimum and may contain letters, and numbers and special characters') }}
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="password_confirmation" class="form-label">{{ __('Confirmation') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                </div>

                <div class="row">
                    <button class="btn btn-primary" type="submit">{{ __('Send') }}</button>
                </div>
            </form>
        </div>
    </main>
@endsection
