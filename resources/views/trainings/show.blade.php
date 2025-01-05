@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $training->name }}</h1>

        <div class="row my-4">
            <div class="col-auto">
                <a href="{{ route('trainings.ranking', $training) }}" class="btn btn-primary me-2">{{ __('Ranking') }}</a>
                <a href="{{ route('trainings.points.create', $training) }}" class="btn btn-primary">{{ __('Batch points') }}</a>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            @foreach ($training->students->sortBy('lastname') as $student)
                <div class="col">
                    <div class="card w-100 hover:shadow">
                        <a class="text-decoration-none text-reset" href="{{ route('students.points.create', $student) }}">
                            <div class="card-body">
                                <h5 class="card-title mb-3">{{ $student->fullname }}</h5>

                                <ul class="list-unstyled small">
                                    @foreach ($student->roles as $role)
                                        <li class="badge list-inline-item me-0 text-black" style="background-color:{{ $role->bgColor }}">{{ $role->name }}</li>
                                    @endforeach
                                </ul>

                                <div class="d-flex justify-content-between">
                                    <strong class="card-text small text-secondary">{{ $student->total_points }} {{ __('Points') }}</strong>
                                    <strong class="card-text small text-{{ App\Models\Absence::color($student->total_absences) }}">{{ floor($student->total_absences / 60) }} h</strong>
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
