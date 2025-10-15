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

        <div class="row">
            @foreach ($training->students->sortBy('lastname') as $student)
                <div class="col-xl-4 col-sm-6 py-2">
                    <div class="card h-100 hover:shadow">
                        <a href="{{ route('students.points.create', $student) }}" class="text-dark">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md">
                                        <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                            <i class="bi bi-person p-3"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 ms-3">
                                        <h5 class="font-size-16 mb-1">{{ $student->fullname }}</h5>
                                        @foreach($student->roles as $role)
                                            <li class="badge list-inline-item me-0 text-black" style="background-color:{{ $role->bgColor }}">{{ $role->name }}</li>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-3 pt-1 d-flex justify-content-between">
                                    <strong class="card-text small text-secondary"><i class="bi bi-award font-size-15 align-middle pe-2 text-primary"></i>{{ $student->total_points ?? '-' }} {{ __('Points') }}</strong>
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
