@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <div class="row my-5">
            <div class="col-auto col-md-6 mx-auto">
                <div class="card w-100">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4">{{ __('New LED strip') }}</h4>

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

                        @include('ledstrips.form', ['action' => route('ledstrip.store')])
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
