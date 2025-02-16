@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <div class="row my-5">
            <div class="col-auto col-lg-8 mx-auto">
                <div class="card w-100">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-5">{{ __('New exam') }}</h4>

                        @if (session()->has('info'))
                            <div class="row my-5">
                                <div class="col-10 col-lg-8 mx-auto">
                                    <div class="alert alert-info">
                                        {!! session()->get('info') !!}
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="row my-5">
                                <div class="col-10 col-lg-8 mx-auto">
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @include('exams.form', ['exam' => $originalExam, 'action' => route('exams.store')])
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
