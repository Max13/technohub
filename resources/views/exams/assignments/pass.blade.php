@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $exam->name }}</h1>

        <div class="row mt-3 mb-4">
            <div id="exam-timer" class="mx-auto p-0 exam-timer" data-duration="{{ $question->duration ?? 0 }}">∞</div>
        </div>

        <div class="row my-4">
            <div class="col-auto col-lg-8 mx-auto">
                <div class="card h-100 w-100">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center text-secondary h4">{!! trans_choice('{1} 1<sup>st</sup>|{2} 2<sup>nd</sup>|{3} 3<sup>rd</sup>|[4,*] :count<sup>th</sup>', $questionNum) !!} {{ __('Question') }}</h2>
                        <h3 class="card-title text-center mb-5">{{ $question->question }}</h3>

                        <div id="exam-answers" class="@if($question->duration > 0) invisible @endif">
                            @if ($question->image)
                                <div class="row text-center my-4">
                                    <div class="col">
                                        <img src="{{ Storage::disk('exams')->url($question->image) }}" class="img-fluid" alt="{{ __('Illustration') }}">
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col col-md-10 mx-auto">
                                    <form id="exam-form" action="{{ route('exams.assignments.answer', ['assignment' => $assignment, 'question' => $question]) }}" method="post">
                                        <div class="row row-cols-1 row-cols-sm-2 g-2 g-md-3">
                                            @csrf

                                            @if ($question->type === \App\Models\Exam\Question::TYPE_OPEN)
                                                <div class="col-auto w-100">
                                                    <textarea name="answer" id="answer" class="form-control" rows="5" placeholder="{{ $question->answer1 ?? __('Type an open answer') }}"></textarea>
                                                    <button type="submit" class="btn btn-primary d-block w-100 mt-4">{{ __('Submit') }}</button>
                                                </div>
                                            @else
                                                @foreach ($question->answers as $answer)
                                                    <div class="col">
                                                        <button type="submit" class="btn btn-lg text-black w-100" style="background-color: {{ \App\Models\Exam\Question::$colors[$loop->index] }}" name="answer" value="{{ $loop->iteration }}">{{ $answer }}</button>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-8 mx-auto">
                                <button type="submit" form="exam-form" class="btn btn-lg btn-secondary w-100" name="answer" value="">{{ __('Skip') }}<i class="bi bi-arrow-right-circle ms-2"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        @if ($question->duration > 0)
            document.querySelectorAll('.exam-timer[data-duration]')
                    .forEach(el => {
                        let initialTimer = {{ config('exam.initial_timer') }};
                        let timerId = 0;
                        let timerFunc = (init, timeout) => {
                            let val = parseInt(el.innerHTML);

                            el.innerHTML = --val;

                            if (val === 0) {
                                window.clearInterval(timerId);

                                if (timeout) {
                                    timeout();
                                }
                            }
                        };

                        el.innerHTML = initialTimer;

                        timerId = window.setInterval(timerFunc, 1000, initialTimer, () => {
                            document.getElementById('exam-answers').classList.remove('invisible');
                            el.innerHTML = el.dataset.duration;
                            timerId = window.setInterval(timerFunc, 1000, el.dataset.duration, () => {
                                let form = document.getElementById('exam-form');
                                let fakeForm = document.createElement('form');
                                let fakeAnswer = document.createElement('input');

                                fakeForm.action = form.action;
                                fakeForm.method = form.method;

                                fakeForm.appendChild(form.querySelector('input[type=hidden][name=_token]').cloneNode(true));

                                fakeAnswer.name = 'answer'
                                fakeAnswer.value = document.getElementById('answer') ? document.getElementById('answer').innerHTML : '';
                                fakeForm.appendChild(fakeAnswer);

                                document.body.append(fakeForm);
                                fakeForm.requestSubmit();
                            });
                        });
                    });
        @endif
    </script>
@endpush
