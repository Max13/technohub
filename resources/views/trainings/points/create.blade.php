@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $training->name }}</h1>
        <h2>{{ __('Batch points allocation') }}</h2>

        <div class="row my-5 mx-auto" style="max-width:576px">

            @if ($errors->any())
                <div class="alert alert-danger mt-4 p-3" role="alert">
                    @if ($errors->count() === 1)
                        <span class="visually-hidden">{{ __('Error') }}: </span>{{ $errors->first() }}
                    @else
                        <ul>
                            @foreach($errors->all() as $message)
                                <li class="text-start"><span class="visually-hidden">{{ __('Error') }}: </span>{{ $message }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <form action="{{ route('trainings.points.store', $training) }}" method="post">
                @csrf
                <div class="mb-3 col-sm-6 offset-md-3">
                    <label for="criterion_id" class="form-label">{{ __('Criterion') }}</label>
                    <select class="form-select" id="criterion_id" name="criterion_id" aria-describedby="criterion_id-help">
                        @foreach($criteria as $criterion)
                            <option data-min="{{ $criterion->min_points }}" data-max="{{ $criterion->max_points }}" value="{{ $criterion->id }}" @if(old('criterion_id') == $criterion->id) selected @endif>{{ $criterion->name }}</option>
                        @endforeach
                    </select>
                    <div id="criterion_id-help" class="form-text">&nbsp;</div>
                </div>
                @foreach ($training->students as $student)
                    <input type="hidden" name="students[{{ $loop->index }}][id]" value="{{ $student->id }}">
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label" id="student-name">
                            {{ $student->lastname }} {{ $student->firstname }}<br>
                            <small class="text-secondary">{{ $student->sum_points ?? 0 }}&nbsp;{{ __('points') }}</small>
                        </label>
                        <div class="col-md-3">
                            <input type="number" class="form-control points-input" aria-labelledby="student-name" placeholder="{{ __('Points') }}" name="students[{{ $loop->index }}][points]" autocomplete="off" @if(old('points.'.$student->id) || old('criterion_id')) value="{{ @old('points.'.$student->id) }}" @else disabled @endif>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control note-input" aria-labelledby="student-name" aria-label="{{ __('Private note') }}" aria-describedby="note-copy-{{ $student->id }}" placeholder="{{ __('Enter a private note') }}" name="students[{{ $loop->index }}][notes]" maxlength="255" value="{{ old('notes.'.$student->id) }}" autocomplete="off">
                                <button class="btn btn-outline-secondary copy-btn" type="button" id="note-copy-{{ $student->id }}">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="row mt-2">
                    <div class="col-md-4 offset-md-4">
                        <button type="submit" class="btn btn-primary w-100">{{ __('Allocate') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        const criterionSelect = document.getElementById('criterion_id');
        const criterionHelp = document.getElementById('criterion_id-help');
        const pointsInputs = document.getElementsByClassName('points-input');
        const noteInputs = document.getElementsByClassName('note-input');
        const noteCopyBtns = document.getElementsByClassName('copy-btn');

        if (criterionSelect.querySelector('[selected]') == null) {
            criterionSelect.value = -1;
        }

        criterionSelect.addEventListener('change', () => {
            if (criterionSelect.selectedOptions.length === 0) {
                return;
            }

            const selectedOption = criterionSelect.selectedOptions[0];

            criterionHelp.innerHTML = '[ ' + selectedOption.dataset.min + ' ; ' + selectedOption.dataset.max + ' ]';
            Array.from(pointsInputs).forEach(input => {
                input.setAttribute('min', selectedOption.dataset.min);
                input.setAttribute('max', selectedOption.dataset.max);
                input.value = selectedOption.dataset.min <= 0 ? selectedOption.dataset.max : selectedOption.dataset.min;
                input.removeAttribute('disabled');
            })
        });

        Array.from(noteCopyBtns).forEach(btn => {
            btn.addEventListener('click', () => {
                const note = btn.previousElementSibling.value;

                Array.from(noteInputs).forEach(input => input.value = note);
            });
        });
    </script>
@endpush
