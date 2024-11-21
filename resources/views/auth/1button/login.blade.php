@extends('layouts.app')

@section('bodyClass', 'd-flex align-items-center py-4 bg-body-tertiary')

@section('content')
    <main class="container-hs text-center">
        <img class="d-block dark:d-none mx-auto mt-4 w-75" src="{{ mix('/img/logo-h_black.svg') }}" alt="ITIC Logo">
        <img class="d-block light:d-none mx-auto mt-4 w-75" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo">

        <div class="mt-5">
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

        <form class="mt-4 text-start" method="POST" action="{{ route('auth.1button.doLogin', $request, false) }}">
            @csrf

            <button class="btn btn-primary w-100 @error('callback') disabled @enderror" type="submit">{{ __('I agree') }}</button>
        </form>

        <p class="text-muted mt-3">{{ __('If you need help, come see us in room 14!') }}</p>
    </main>

    <x-hs-terms-modal id="termsModal" />
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
