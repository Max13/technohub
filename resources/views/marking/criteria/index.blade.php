@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Criteria') }}</h1>

        <div class="row mx-auto" style="max-width: 400px">
            <div class="my-3 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#new-criterion-modal">{{ __('New criterion') }}</button>
            </div>

            <div class="row my-4">
                <ul class="list-group list-group-flush">
                @foreach ($criteria as $criterion)
                    <li class="list-group-item">
                        <p class="d-flex justify-content-between">
                            {{ $criterion->name }}
                            <a class="ms-2" href="{{ route('marking/criteria.edit', $criterion) }}">
                                <i class="bi-pencil-square"></i>
                            </a>
                        </p>
                        <p class="mb-0">{{ $criterion->min_points }} <i class="bi-arrow-right-short"></i> {{ $criterion->max_points }}</p>
                    </li>
                @endforeach
                </ul>
            </div>
        </div>
    </main>

    <div class="modal fade" tabindex="-1" id="new-criterion-modal" aria-labelledby="new-criterion-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="new-criterion-modal-title">{{ __('New criterion') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body container">
                    <form id="form-new-criterion" action="{{ route('marking/criteria.create') }}" method="post">
                        @csrf

                        <div class="row mb-3">
                            <label for="exampleFormControlInput1" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="form-new-criterion" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
