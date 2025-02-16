@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Assign an exam') }}</h1>
        <h3>{{ $exam->name }}</h3>

        <div class="row my-5">
            <div class="col-auto col-lg-8 mx-auto">
                <div class="card w-100">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-5">{{ __('Choose the assignees') }}</h4>

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

                        <form action="{{ route('exams.doAssign', $exam) }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-10 mx-auto">
                                    <label for="valid_at" class="form-label">{{ __('Valid from') }} <small class="text-secondary">({{ __('Optional') }})</small></label>
                                    <input type="date" class="form-control" id="valid_at" name="valid_at" min="{{ today()->addDay()->toDateString() }}" max="{{ $endOfYear->subDay()->toDateString() }}" value="{{ old('valid_at') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-10 mx-auto">
                                    <label for="valid_until" class="form-label">{{ __('Valid until') }} <small class="text-secondary">({{ __('Optional') }})</small></label>
                                    <input type="date" class="form-control" id="valid_until" name="valid_until" min="{{ today()->addDays(2)->toDateString() }}" max="{{ $endOfYear->toDateString() }}" value="{{ old('valid_until') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-10 mx-auto">
                                    <label for="students" class="form-label">{{ __('Students') }}</label>
                                    <ul class="list-unstyled ms-4" id="students">
                                        @foreach ($trainings as $training)
                                            <li class="form-check mb-4">
                                                <input class="form-check-input" type="checkbox" id="training-{{ $training->id }}" data-training="{{ $training->id }}">
                                                <label class="form-check-label mb-2" for="training-{{ $training->id }}">
                                                    <abbr class="initialism fs-5" title="{{ $training->fullname }}">{{ $training->name }}</abbr>&nbsp;:
                                                </label>

                                                <ul class="list-unstyled">
                                                    @foreach ($training->classrooms->pluck('users')->flatten() as $student)
                                                        <li class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="student-{{ $student->id }}" name="students[]" value="{{ $student->id }}" data-training="{{ $training->id }}" @if(in_array($student->id, old('students', []))) checked @endif>
                                                            <label class="form-check-label" for="student-{{ $student->id }}">{{ $student->fullname }}</label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <div class="row mx-auto">
                                <div class="col-6 mx-auto">
                                    <button type="submit" class="btn btn-primary w-100">{{ __('Submit') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        const endOfYear = '{{ $endOfYear->toDateString() }}';
        const validAtEl = document.getElementById('valid_at');

        validAtEl.addEventListener('change', e => {
            const validAt = e.target.value;
            const validUntilEl = document.getElementById('valid_until');
            let nextDate;

            if (validAt === '') {
                nextDate = Date.now() + (24 * 60 * 60 * 1000);
            } else {
                nextDate = Date.parse(validAt) + (24 * 60 * 60 * 1000);
            }

            validUntilEl.min = (new Date(nextDate)).toISOString().slice(0, 10);
        });

        validAtEl.dispatchEvent(new Event('change', {
            view: window,
            bubbles: true,
        }));

        document.querySelectorAll('[id^=training-]')
                .forEach(trainingEl => {
                    const studentBox = document.querySelectorAll('[data-training="'+trainingEl.dataset.training+'"]');

                    trainingEl.addEventListener('change', () => {
                        studentBox.forEach(studentEl => {
                            studentEl.checked = trainingEl.checked;
                        });
                    });
                });
    </script>
@endpush
