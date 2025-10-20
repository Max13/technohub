@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('LED Strips') }}</h1>

        <div class="row my-4">
            <div class="col-auto">
                <a href="{{ route('ledstrip.create') }}" class="btn btn-primary me-2">{{ __('Create') }}</a>
            </div>
        </div>

        <div class="row row-cols-lg-4 g-2 my-2">
            @foreach ($ledstrips as $ledstrip)
                <div class="col">
                    <div class="card h-100 w-100 hover:shadow">
                        <a class="text-decoration-none text-reset" href="{{ route('ledstrip.show', $ledstrip) }}">
                            <div class="card-body">
                                <p class="card-title">{{ $ledstrip->name }}</p>

                                <div class="d-flex justify-content-between">
                                    <p class="card-text small text-muted mb-0">{!! number_format($ledstrip->length, thousands_separator: '&nbsp;') !!}&nbsp;{{ __('LEDs') }}</p>
                                    <p class="card-text small text-{{ $ledstrip->power_class ?? 'muted' }} mb-0">{!! number_format($ledstrip->power_supply, thousands_separator: '&nbsp;') !!}&nbsp;{{ __('Amps') }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
