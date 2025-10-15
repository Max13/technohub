@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <div class="main-body">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center text-center">
                                <img src="{{ asset('/img/person.png') }}" alt="Admin" class="rounded-circle p-1" width="130">
                                <div class="mt-3">
                                    <h4>{{ $user->fullname }}</h4>
                                    @foreach($user->roles as $role)
                                        <p class="badge list-inline-item me-0 text-black mb-2" style="background-color:{{ $role->bgColor }}">{{ $role->name }}</p>
                                    @endforeach
                                    <p class="text-muted font-size-sm">{{ optional($user->currentTraining)->name ?? '–' }}</p>
                                    <a class="list-inline-item ms-2" data-bs-toggle="modal" data-bs-target="#user-roles-modal">
                                        <button class="btn btn-link btn-sm"><i class="bi bi-key"></i></button>
                                    </a>
                                    <button class="btn btn-outline-primary">Noter</button>
                                    <button class="btn btn-outline-primary">Contacter</button>
                                </div>
                                </div>
                                <hr class="my-4">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0"><i class="bi bi-award"></i>&nbsp; Points</h6>
                                        <span class="text-secondary">{{ $user->total_points ?? '-' }}</span>
                                    </li>
                                </ul>
                            @if ($user->trainings->isNotEmpty())
                                <hr class="my-2">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <h6 class="mb-2">
                                            {{ __('Trainings') }}
                                        </h6>
                                        <ul>
                                            @foreach ($user->trainings as $training)
                                                <li>{{ $training->name }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">First name</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $user->firstname }}
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Last name</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $user->lastname }}
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Email</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $user->email }}
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Login Ypareo</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $user->ypareo_login }}
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Ypareo ID</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        {{ $user->ypareo_id }}
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <a class="btn btn-primary" target="__blank" href="#">Edit</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <canvas id="courses-details-graph"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal" tabindex="-1" id="user-roles-modal" aria-labelledby="user-roles-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="user-roles-modal-title">{{ __('Assign roles') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body container">
                    <form id="form-user-roles" action="{{ route('users.roles.update', $user->id) }}" method="post">
                        @csrf
                        @method('patch')

                        <div class="row row-cols-1 row-cols-sm-2">
                            @foreach ($roles as $role)
                                <div class="col">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="role-{{ $role->id }}" name="roles[]" value="{{ $role->id }}" @if(isset($user->ypareo_id) && $role->is_from_ypareo) disabled @endif @if($user->roles->contains($role)) checked @endif>
                                        <label class="form-check-label badge text-black" style="background-color:{{ $role->bgColor }}" for="role-{{ $role->id }}">{{ $role->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="form-user-roles" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" integrity="sha512-CQBWl4fJHWbryGE+Pc7UAxWMUMNMWzWxF4SQo9CgkJIN1kx6djDQZjh3Y8SZ1d+6I+1zze6Z7kHXO7q3UyZAWw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        // Dark mode
        if (document.documentElement.getAttribute('data-bs-theme') === 'dark') {
            Chart.defaults.backgroundColor = window.getComputedStyle(document.body).getPropertyValue('--bs-body-bg');
            Chart.defaults.borderColor = window.getComputedStyle(document.body).getPropertyValue('--bs-body-color');
            Chart.defaults.color = window.getComputedStyle(document.body).getPropertyValue('--bs-body-color');
            Chart.defaults.scale.ticks.backdropColor = window.getComputedStyle(document.body).getPropertyValue('--bs-body-bg');
        }

        let coursesDetails = {
            durations: {!! $courses_details->pluck('courses_duration')->toJson() !!}.map(duration => duration / 60),
            absences: {!! $courses_details->pluck('absences_duration')->toJson() !!}.map(duration => duration / 60),
        };

        coursesDetails.totalDuration = coursesDetails.durations.reduce((carry, duration) => carry + duration);
        coursesDetails.relativeCourses = coursesDetails.durations.map(duration => Math.round(100 / (coursesDetails.totalDuration / duration)));
        coursesDetails.relativeAbsences = coursesDetails.absences.map((duration, i) => Math.round(100 / (coursesDetails.durations[i] / duration)));

        let coursesDetailsGraphCtx = document.getElementById('courses-details-graph');
        let coursesDetailsGraph = new Chart(coursesDetailsGraphCtx, {
            type: 'bar',
            data: {
                labels: {!! $courses_details->keys()->toJson() !!}.map(name => {
                    if (name.startsWith('BLOC ')) {
                        name = name.replace(/BLOC ?/i, 'B');
                    }
                    if (name.includes(' : ')) {
                        nameParts = name.split(' : ', 2);
                        name = nameParts[0] + ' : ' + nameParts[1].split(' ', 1)[0];
                    }
                    return name;
                }),
                datasets: [
                    {
                        label: 'Cours',
                        data: coursesDetails.durations.map(duration => duration === 0 ? null : duration),
                        minBarLength: 5,
                    },{
                        label: 'Absences',
                        data: coursesDetails.absences.map(duration => duration === 0 ? null : duration),
                        minBarLength: 5,
                    },
                ],
            },
        });
    </script>
@endpush
