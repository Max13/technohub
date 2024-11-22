@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $student->fullname }} - {{ $student->currentTraining->name }}</h1>
        <h2>{{ __('Points allocation') }}</h2>

        <div class="row my-5 mx-auto" style="max-width:300px">

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

            <form action="{{ route('students.points.store', $student) }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="criterion_id" class="form-label">{{ __('Criterion') }}</label>
                    <select class="form-select" id="criterion_id" name="criterion_id" aria-describedby="criterion_id-help">
                        @foreach($criteria as $criterion)
                            <option data-min="{{ $criterion->min_points }}" data-max="{{ $criterion->max_points }}" value="{{ $criterion->id }}" @if(old('criterion_id') == $criterion->id) selected @endif>{{ $criterion->name }}</option>
                        @endforeach
                    </select>
                    <div id="criterion_id-help" class="form-text">&nbsp;</div>
                </div>
                <div class="mb-3">
                    <label for="points" class="form-label">{{ __('Points') }}</label>
                    <input type="number" class="form-control" placeholder="{{ __('Select a criterion') }}" id="points" name="points" @if(old('points')) value="{{ @old('points') }}" @else disabled @endif>
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('Notes') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                    <input type="text" class="form-control" placeholder="{{ __('Enter a private note') }}" id="notes" name="notes" maxlength="255" value="{{ old('notes') }}">
                </div>
                <button type="submit" class="btn btn-primary mt-2 w-100">{{ __('Allocate') }}</button>
            </form>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        const criterionSelect = document.getElementById('criterion_id');
        const criterionHelp = document.getElementById('criterion_id-help');
        const pointsInput = document.getElementById('points');

        if (criterionSelect.querySelector('[selected]') == null) {
            criterionSelect.value = -1;
        }

        criterionSelect.addEventListener('change', () => {
            if (criterionSelect.selectedOptions.length === 0) {
                return;
            }

            const selectedOption = criterionSelect.selectedOptions[0];

            criterionHelp.innerHTML = '[ ' + selectedOption.dataset.min + ' ; ' + selectedOption.dataset.max + ' ]';
            pointsInput.setAttribute('min', selectedOption.dataset.min);
            pointsInput.setAttribute('max', selectedOption.dataset.max);
            pointsInput.value = selectedOption.dataset.min <= 0 ? selectedOption.dataset.max : selectedOption.dataset.min;
            pointsInput.removeAttribute('disabled');
        });
    </script>
@endpush
