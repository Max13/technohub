<div>
    <form action="{{ $action }}" method="post" enctype="multipart/form-data" autocomplete="off">
        @if (strtolower($method ?? 'post') !== 'post')
            @method($method)
        @endif

        <div class="row mb-4">
            <div class="col-10 mx-auto">
                <label for="name" class="form-label">{{ __('Name') }}&nbsp;&nbsp;<small class="text-secondary">({{ __('Must be unique') }})</small></label>
                <input type="text" class="form-control @if($errors->has('name')) is-invalid @endif" id="name" name="name" value="{{ old('name', $exam->name) }}" aria-describedby="name-validation" required>
                <div id="name-validation" class="invalid-feedback">{{ $errors->first('name') }}</div>
            </div>
        </div>

        {{--
        <div class="row mb-4">
            <div class="col-10 mx-auto">
                <label for="seb_config_file" class="form-label">{!! __('SEB Configuration <u class="link-offset-1">File</u>') !!}&nbsp;&nbsp;<a class="icon-link" href="https://safeexambrowser.org/windows/win_usermanual_en.html#configuration" target="_blank"><i class="bi bi-question-circle"></i></a><br><small class="text-secondary">({!! __('Required <u class="link-offset-1">only if</u> any key is provided below') !!})</small></label>
                <input type="file" accept=".seb" class="form-control @if($errors->has('seb_config_file')) is-invalid @endif" id="seb_config_file" name="seb_config_file" aria-describedby="seb_config_file-validation">
                <div id="seb_config_file-validation" class="invalid-feedback">{{ $errors->first('seb_config_file') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-10 mx-auto">
                <label for="seb_config_key" class="form-label">{!! __('SEB Configuration <u class="link-offset-1">Key</u>') !!}&nbsp;&nbsp;<a class="icon-link" href="https://safeexambrowser.org/windows/win_usermanual_en.html#ExamPane" target="_blank"><i class="bi bi-question-circle"></i></a><br><small class="text-secondary">({{ __('Recommended if a configuration file is provided') }})</small></label>
                <input type="text" class="form-control @if($errors->has('seb_config_key')) is-invalid @endif" aria-describedby="seb_config_key-validation" id="seb_config_key" name="seb_config_key" value="{{ old('seb_config_key') }}">
                <div id="seb_config_key-validation" class="invalid-feedback">{{ $errors->first('seb_config_key') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-10 mx-auto">
                <label for="seb_exam_key" class="form-label">{!! __('SEB <u class="link-offset-1">Exam</u> Key') !!}&nbsp;&nbsp;<a class="icon-link" href="https://safeexambrowser.org/windows/win_usermanual_en.html#ExamPane" target="_blank"><i class="bi bi-question-circle"></i></a><br><small class="text-secondary">({{ __('Recommended if a configuration file is provided') }})</small></label>
                <input type="text" class="form-control @if($errors->has('seb_exam_key')) is-invalid @endif" aria-describedby="seb_exam_key-validation" id="seb_exam_key" name="seb_exam_key" value="{{ old('seb_exam_key') }}">
                <div id="seb_exam_key-validation" class="invalid-feedback">{{ $errors->first('seb_exam_key') }}</div>
            </div>
        </div>

        <hr>
        --}}

        <h4 class="text-center mb-4">{{ __('Questions') }}</h4>

        <div class="row my-3">
            <div class="col-10 col-md-8 mx-auto text-center">
                <p class="small mb-0">{{ __('To add a question with an open answer, keep the question without answer') }}.</p>
            </div>
        </div>

        <div id="questions">
            <div class="text-center" data-spinner>
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ __('Loading') }}…</span>
                </div>
            </div>
        </div>

        <div class="row my-4">
            <div class="col-auto mx-auto">
                <button type="button" id="add-question" class="btn btn-sm btn-outline-info">{{ __('Add question') }}</button>
            </div>
        </div>

        <div class="row mx-auto">
            <div class="col-6 mx-auto">
                <button type="submit" class="btn btn-primary w-100">{{ __('Submit') }}</button>
            </div>
        </div>
    </form>

    <div id="question-model" class="row mb-4 pb-2" data-question-row hidden>
        <div class="col-10 mx-auto">
            <div class="row g-1 mb-1">
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary" data-move-up><i class="bi bi-arrow-bar-up"></i></button>
                </div>
                <div class="col-auto text-center" style="width: 46px">
                    <label class="col-form-label font-monospace" data-loop-index></label>
                </div>
                <div class="col">
                    <input type="text" class="form-control" placeholder="{{ __('Question') }}" data-question>
                    <div class="invalid-feedback mb-2" data-question-validation></div>
                </div>
            </div>
            <div class="row g-1 mb-1">
                <div class="col-auto" style="width: 92px">&nbsp;</div>
                <div class="col input-group">
                    <label class="input-group-text">{{ __('Image') }}</label>
                    <input type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" data-image>
                    <div class="invalid-feedback mb-2" data-image-validation></div>
                </div>
            </div>
            <div class="row g-1 mb-1">
                <div class="col-auto" style="width: 46px">&nbsp;</div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary" title="{{ __('True').'/'.__('False') }}" data-answer="truefalse"><i class="bi bi-back"></i></button>
                </div>
                <div class="col text-center">
                    <div class="row row-cols-4 row-cols-md-2 g-1">
                        <div class="col text-center">
                            <textarea class="form-control" rows="1" placeholder="{{ __('Answer') }} 1" data-answers></textarea>
                            <input type="checkbox" class="form-check-input" data-valid-answers>
                        </div>
                        <div class="col text-center">
                            <textarea class="form-control" rows="1" placeholder="{{ __('Answer') }} 2" data-answers></textarea>
                            <input type="checkbox" class="form-check-input" data-valid-answers>
                        </div>
                        <div class="col text-center">
                            <textarea class="form-control" rows="1" placeholder="{{ __('Answer') }} 3" data-answers></textarea>
                            <input type="checkbox" class="form-check-input" data-valid-answers>
                        </div>
                        <div class="col text-center">
                            <textarea class="form-control" rows="1" placeholder="{{ __('Answer') }} 4" data-answers></textarea>
                            <input type="checkbox" class="form-check-input" data-valid-answers>
                        </div>
                        <div class="col w-100 text-center mt-0">
                            <div class="invalid-feedback mt-0 mb-2" data-answers-validation></div>
                            <div class="invalid-feedback mt-0 mb-2" data-valids-validation></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-1 mb-1">
                <div class="col-auto mt-auto">
                    <button type="button" class="btn btn-outline-secondary" data-move-down><i class="bi bi-arrow-bar-down"></i></button>
                </div>
                <div class="col-auto" style="width: 46px">&nbsp;</div>
                <div class="col text-center">
                    <div class="row g-1">
                        <div class="col input-group">
                            <span class="input-group-text">{{ __('Duration') }}</span>
                            <select class="form-select" data-duration>
                                @foreach($available_timers as $time)
                                    <option value="{{ $time }}">{{ $time ?: '∞' }} sec</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col input-group">
                            <input type="number" class="form-control" min="0" step="0.01" data-points>
                            <span class="input-group-text">{{ __('Point(s)') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        {{--
        const sebConfigFileEl = document.getElementById('seb_config_file');
        const sebKeyEls = [
            document.getElementById('seb_config_key'),
            document.getElementById('seb_exam_key'),
        ];

        sebConfigFileEl.addEventListener('change', () => {
            // if there are any sebKeyEls with a value
            sebConfigFileEl.required = sebKeyEls.filter(el => el.value !== '').length > 0;
        });

        sebConfigFileEl.dispatchEvent(new Event('change', {
            view: window,
            bubbles: true,
        }));

        // ---
        --}}

        const questionsDiv = document.getElementById('questions');
        const questionModel = document.getElementById('question-model');
        const questionList = {!! json_encode(array_values(old('questions', optional($exam->questions)->toArray() ?? []))) !!};
        const questionErrors = {!! $errors->toJson() !!};
        const addQuestionListeners = questionDiv => {
            // Up btn
            const upBtn = questionDiv.querySelector('[data-move-up]');
            upBtn.addEventListener('click', () => {
                if (questionDiv.previousElementSibling === null) {
                    return;
                }

                questionsDiv.insertBefore(questionDiv, questionDiv.previousElementSibling);

                questionDiv.dispatchEvent(new Event('afterMove'));
                questionDiv.nextElementSibling.dispatchEvent(new Event('afterMove'));
            });

            // Down btn
            const downBtn = questionDiv.querySelector('[data-move-down]');
            downBtn.addEventListener('click', () => {
                if (questionDiv.nextElementSibling === null) {
                    return;
                }

                questionsDiv.insertBefore(questionDiv, questionDiv.nextElementSibling.nextElementSibling);

                questionDiv.dispatchEvent(new Event('afterMove'));
                questionDiv.previousElementSibling.dispatchEvent(new Event('afterMove'));
            });

            questionDiv.addEventListener('afterMove', ev => {
                const questionsElements = questionsDiv.querySelectorAll('[data-question-row]');

                for (let i=0; i<questionsElements.length; ++i) {
                    if (questionsElements[i] === ev.target) {
                        ev.target.querySelector('[data-loop-index]').innerHTML = '#' + (i + 1);
                        break;
                    }
                }

                upBtn.disabled = ev.target.previousElementSibling === null;
                downBtn.disabled = ev.target.nextElementSibling === null;
            });

            // True/False btn
            questionDiv.querySelector('[data-answer=truefalse]').addEventListener('click', () => {
                const answers = questionDiv.querySelectorAll('[data-answers]');
                answers[0].value = '{{ __('True') }}';
                answers[1].value = '{{ __('False') }}';
                answers[2].value = '';
                answers[3].value = '';
            });
        };
        const addQuestion = (question = {}) => {
            const questionRows = questionsDiv.querySelectorAll('[data-question-row]');
            let newQuestion = questionModel.cloneNode(true);
            const newQuestionIndex = questionRows.length;
            const newQuestionQuestion = newQuestion.querySelector('[data-question]');

            newQuestion.removeAttribute('id');
            newQuestion.removeAttribute('hidden');

            newQuestion.querySelector('[data-loop-index]').innerHTML = '#' + (newQuestionIndex + 1);
            newQuestionQuestion.name = 'questions[' + newQuestionIndex + '][question]';
            newQuestionQuestion.value = question.question ?? '';
            newQuestion.querySelector('[data-image]').name = 'questions[' + newQuestionIndex + '][image]';
            newQuestion.querySelectorAll('[data-answers]').forEach((el, index) => {
                el.name = 'questions[' + newQuestionIndex + '][answers][]';
                if (question['answers']) {
                    el.value = question['answers'][index];
                } else if (question['answer1']) {
                    el.value = question['answer' + (index + 1)];
                }
            });
            newQuestion.querySelectorAll('[data-valid-answers]').forEach((el, index) => {
                const valids = (question['valids'] ?? []).map(val => parseInt(val));
                el.name = 'questions[' + newQuestionIndex + '][valids][]';
                el.value = index + 1;
                el.checked = valids.indexOf(index + 1) !== -1;
            });
            newQuestion.querySelector('[data-duration]').name = 'questions[' + newQuestionIndex + '][duration]';
            newQuestion.querySelector('[data-duration]').value = question['duration'] ?? 0;
            newQuestion.querySelector('[data-points]').name = 'questions[' + newQuestionIndex + '][points]';
            newQuestion.querySelector('[data-points]').value = question['points'];

            questionsDiv.append(newQuestion);
            addQuestionListeners(newQuestion);

            newQuestion.dispatchEvent(new Event('afterMove'));
            if (questionRows.length > 0) {
                questionRows[questionRows.length - 1].dispatchEvent(new Event('afterMove'));
            }

            return newQuestion;
        };
        const invalidateQuestion = (key, msg) => {
            const [q, qIndex, subKey] = key.split('.');
            let questionEl = questionsDiv.querySelectorAll('[data-question-row]:nth-child('+ (parseInt(qIndex) + 1) +') [name^="questions['+ parseInt(qIndex) +']['+ subKey +']"]');
            let feedbackEl = questionsDiv.querySelector('[data-question-row]:nth-child('+ (parseInt(qIndex) + 1) +') [data-'+ subKey +'-validation]');
            const feedbackId = subKey + '-validation';

            console.log(questionEl, feedbackEl);

            questionEl.forEach(el => {
                el.classList.add('is-invalid');
                if (el.hasAttribute('aria-describedby')) {
                    el.setAttribute('aria-describedby', el.getAttribute('aria-describedby') + ' ' + feedbackId);
                } else {
                    el.setAttribute('aria-describedby', feedbackId);
                }
            });
            feedbackEl.classList.add('d-block');
            feedbackEl.id = feedbackId;
            feedbackEl.innerHTML = msg;
        };

        document.getElementById('add-question').addEventListener('click', addQuestion);

        questionsDiv.querySelector('[data-spinner]').remove();
        if (questionList.length > 0) {
            questionList.forEach(addQuestion);
        }

        Object.keys(questionErrors)
              .filter(key => key.startsWith('questions.'))
              .forEach(key => invalidateQuestion(key, questionErrors[key]));
    </script>
@endpush
