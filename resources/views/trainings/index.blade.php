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
