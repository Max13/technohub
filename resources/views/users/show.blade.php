@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $user->fullname }}</h1>

        <div class="row my-4">
            <!-- User details -->
            <div class="col-auto col-md-5 mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <ul class="small list-inline mb-4">
                            @foreach($user->roles as $role)
                                <li class="badge list-inline-item me-0 text-black" style="background-color:{{ $role->bgColor }}">{{ $role->name }}</li>
                            @endforeach
                            <li class="list-inline-item ms-2" data-bs-toggle="modal" data-bs-target="#user-roles-modal">
                                <button class="btn btn-link btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </li>
                        </ul>

                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="p-0">{{ __('Lastname') }}</td>
                                    <td class="p-0">{{ $user->lastname }}</td>
                                </tr>
                                <tr>
                                    <td class="p-0">{{ __('Firstname') }}</td>
                                    <td class="p-0">{{ $user->firstname }}</td>
                                </tr>
                                <tr>
                                    <td class="p-0">{{ __('Ypareo Email') }}</td>
                                    <td class="p-0">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="p-0">{{ __('Ypareo ID') }}</td>
                                    <td class="p-0">{{ $user->ypareo_id ?? '–' }}</td>
                                </tr>
                                <tr>
                                    <td class="p-0">{{ __('Ypareo Login') }}</td>
                                    <td class="p-0">{{ $user->ypareo_login ?? '–' }}</td>
                                </tr>
                                <tr>
                                    <td class="p-0">{{ __('Current training') }}</td>
                                    <td class="p-0">{{ optional($user->currentTraining)->name ?? '–' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        @if ($user->trainings->isNotEmpty())
                            <ul class="list-unstyled">
                                <li>{{ __('Trainings') }}
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

            <!-- Graph -->
            <div class="col-auto col-md mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <canvas id="courses-details-graph"></canvas>
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
                        data: coursesDetails.durations,
                    },{
                        label: 'Absences',
                        data: coursesDetails.absences,
                    },
                ],
            },
        });
    </script>
@endpush
