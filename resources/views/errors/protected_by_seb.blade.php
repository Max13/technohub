@extends('layouts.app')

@section('content')
    <main class="container py-4">
        <div class="row my-4">
            <div class="col-auto col-lg-8 mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">{!! __('Protected by <abbr title="Safe Exam Browser">SEB</abbr>') !!}</h2>

                        <p>{{ __('This resource can only be accessed using SEB.') }}</p>
                        <p>{{ __('You may find the link to access this resource using SEB at the previous page.') }}</p>
                        <p class="small text-secondary">{{ $message }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
