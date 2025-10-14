@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('My trainings') }}</h1>

        <div class="row g-3 my-3">
            <div class="col-md-3">
                <input type="text" class="form-control" id="name" name="name" data-location="{{ route('students.points.create', '%id%') }}" placeholder="{{ __('Student\'s name') }}" aria-label="{{ __('Student\'s name') }}" autocomplete="off" required>
            </div>
        </div>

        <div class="row">
            @foreach ($trainings as $training)
                <div class="col-xl-3 col-sm-6 py-2">
                    <div class="card h-100 hover:shadow">
                        <a class="text-decoration-none text-reset" href="{{ route('trainings.show', $training) }}">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md">
                                        <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                            <i class="bi bi-people p-3"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 ms-3">
                                        <h5 class="font-size-16 mb-1">
                                            {{ $training->name }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="mt-3 pt-1">
                                    <P class="text-muted mb-0">
                                        <i class="bi bi-info-circle font-size-15 align-middle pe-2 text-primary"></i>
                                        <span class="badge badge-soft-success mb-0">{{ $training->students->count() }} {{ __('Students') }}</span>
                                    </P>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!--
        <div class="row row-cols-1 row-cols-md-3 g-4 my-4">
            @foreach ($trainings as $training)
                <div class="col">
                    <div class="card w-100 hover:shadow">
                        <a class="text-decoration-none text-reset" href="{{ route('trainings.show', $training) }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $training->name }}</h5>
                                <p class="card-text small text-muted">{{ $training->students->count() }} {{ __('Students') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        -->
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        (() => {
            const ac = new Autocomplete(document.getElementById('name'), {
                data: {!! $students->toJson() !!},
                label: 'fullname',
                value: 'id',
                maximumItems: 5,
                onSelectItem: ({label, value}) => {
                    window.location.href = ac.field.dataset.location.replace('%id%', value);
                }
            });
        })();
    </script>
@endpush
