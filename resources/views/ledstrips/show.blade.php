@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $ledstrip->name }}</h1>

        <div class="row my-4 g-3">
            <div class="col-auto col-10 col-lg-6 col-xl-5 mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body">

                        @if ($errors->any())
                            <div class="row my-4">
                                <div class="col-10 mx-auto">
                                    <div class="alert alert-danger mb-0">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <table class="table table-borderless mb-4 align-middle">
                            <tbody>
                                <tr>
                                    <td class="p-1 w-50">{{ __('Length') }}</td>
                                    <td class="p-1">{!! number_format($ledstrip->length, thousands_separator: '&nbsp;') !!}&nbsp;{{ __('LEDs') }}</td>
                                </tr>
                                <tr>
                                    <td class="p-1 w-50">{{ __('Power Supply Necessary') }}</td>
                                    <td class="p-1">{!! number_format($ledstrip->power_necessary, thousands_separator: '&nbsp;') !!}&nbsp;{{ __('Amps') }}</td>
                                </tr>
                                <tr>
                                    <td class="p-1 w-50">{{ __('Power Sypply Declared') }}</td>
                                    <td class="p-1 text-{{ $ledstrip->power_class }}">{!! number_format($ledstrip->power_supply, thousands_separator: '&nbsp;') !!}&nbsp;{{ __('Amps') }}</td>
                                </tr>
                                <tr>
                                    <td class="p-1 w-50">{{ __('Power Limiting Factor') }}</td>
                                    <td class="p-1">{{ $ledstrip->limiting_factor * 100 }}&percnt;</td>
                                </tr>
                                <tr>
                                    <td class="p-1 w-50">{{ __('Latest value') }}</td>
                                    <td class="p-1 font-monospace">
                                        <form action="{{ route('ledstrip.control', $ledstrip) }}" method="post" id="form-color">
                                            @csrf
                                        </form>

                                        <div class="row row-cols-2 align-items-center g-2">
                                            <div class="col-12 input-group">
                                                <input type="text" class="form-control" form="form-color" id="color" name="color" value="{{ $lastValueHtml }}" aria-label="{{ __('Color') }}" aria-describedby="color-addon1">
                                                <span class="input-group-text p-0" id="color-addon1">
                                                    <input type="color" class="form-control form-control-color" id="color-picker" value="{{ $lastValueHtml }}" title="{{ __('Change the strip color') }}" aria-label="{{ __('Color') }}">
                                                </span>
                                                <button type="submit" class="btn btn-outline-secondary" form="form-color"><i class="bi bi-check2"></i></button>
                                            </div>
                                            <div class="col-6">
                                                <button type="submit" class="btn btn-outline-primary btn-sm w-100" name="color" value="*" form="form-color">{{ __('Rainbow') }}</button>
                                            </div>
                                            <div class="col">
                                                <button type="submit" class="btn btn-outline-primary btn-sm w-100" name="color" value="#000000" form="form-color">{{ __('Off') }}</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        let colorField = document.getElementById('color');

        document.getElementById('color-picker').addEventListener('change', function(e) {
            colorField.value = e.target.value;
        });
    </script>
@endpush
