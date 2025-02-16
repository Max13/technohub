@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Exam') }} : {{ $exam->name }}</h1>

        <div class="row my-4">
            <div class="col-auto col-lg-10 mx-auto">
                <div class="row mb-2">
                    <div class="col-auto ps-0">
                        <a href="{{ route('exams.doAssign', $exam) }}" class="btn btn-success">{{ __('Assign') }}</a>
                        <a href="{{ route('exams.edit', $exam) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                        <a href="{{ route('exams.self-assign', $exam) }}" class="btn btn-secondary">{{ __('Self assign') }}</a>
                    </div>
                </div>

                {{-- Questions --}}
                <div class="row mb-2">
                    <div class="card h-100 w-100">
                        <div class="card-body">
                            @if ($exam->seb_config_file)
                                <h5><i class="bi bi-shield-lock"></i>&nbsp;<span class="text-secondary">{!! __('Protected by <abbr title="Safe Exam Browser">SEB</abbr>') !!}</span></h5>
                            @endif

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">{{ __('Questions') }}</th>
                                        <th>{{ __('Image') }}</th>
                                        <th>{{ __('Answers') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Points') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider">
                                    @foreach ($questions as $question)
                                        <tr>
                                            <td class="px-1">{{ $loop->iteration }}.&nbsp;{{ $question->question }}</td>
                                            <td class="px-1">
                                                @if ($question->image)
                                                    <img class="border" src="{{ Storage::disk('exams')->url($question->image) }}" alt="{{ __('Illustration') }}" data-bs-toggle="modal" data-bs-target="#img-preview-modal" data-question="{{ $question->question }}" style="cursor: zoom-in; height: 2em; width: 2em">
                                                @else
                                                    &nbsp;
                                                @endif
                                            </td>
                                            <td class="px-1">
                                                <ul class="ps-2">
                                                    @foreach ($question->answers as $answer)
                                                        <li class="@if(in_array($loop->iteration, $question->valids ?? [])) text-success @endif">
                                                            {{ $answer }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                            <td class="px-1">{{ $question->duration }}</td>
                                            <td class="px-1">{!! $question->points ?? '&mdash;' !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="card h-100 w-100">
                        <div class="card-body">
                            <table class="table caption-top">
                                <caption>{{ __('Assigned') }}</caption>
                                <thead>
                                    <tr>
                                        <th>{{ __('Short ID') }}</th>
                                        <th>{{ __('Validity') }}</th>
                                        <th>{{ __('Created at') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider">
                                    @foreach ($assignGroups as $group)
                                        <tr>
                                            <td class="font-monospace">{{ substr($group->group_uuid, -12) }}</td>
                                            <td>{{ substr($group->valid_at ?? $group->created_at, 0, 10) }} &rarr; {{ substr($group->valid_until ?? '∞', 0, 10) }}</td>
                                            <td>{{ $group->created_at }}</td>
                                            <td>
                                                <a class="btn btn-sm btn-primary" href="#group-details" title="{{ __('See details') }}" aria-label="{{ __('See details') }}"><i class="bi bi-list-task" aria-hidden="true"></i></a>
                                                <a class="btn btn-sm btn-secondary" href="{{ route('exams.report', ['group_uuid' => $group->group_uuid]) }}" title="{{ __('Download report') }}" aria-label="{{ __('Download report') }}"><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-img-preview id="img-preview-modal" />
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
