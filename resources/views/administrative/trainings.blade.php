@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Trainings') }} ({{ $trainings->count() }}) <a class="btn btn-primary ms-2 rounded-circle" href="{{ route('administrative.trainings.export', ['filters' => request()->query('filters')]) }}" data-bs-toggle="tooltip" title="{{ __('Export to CSV') }}"><i class="bi bi-cloud-arrow-down fs-3"></i></a></h1>

        <div class="row mx-2 my-4">
            <div class="col-auto">
                <form id="filters-form">
                    <div class="row">
                        <div class="col-auto">
                            <h2 class="fs-5">{{ __('Show archived') }}</h2>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input my-2" type="radio" id="filter-show-archived-0" name="filters[show-archived]" value="0" @if (request('filters.show-archived') !== '1') checked @endif>
                                <label class="form-check-label fs-4" for="filter-show-archived-0"><i class="bi bi-x-lg text-danger"></i></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input my-2" type="radio" id="filter-show-archived-1" name="filters[show-archived]" value="1" @if (request('filters.show-archived') === '1') checked @endif>
                                <label class="form-check-label fs-4" for="filter-show-archived-1"><i class="bi bi-check-lg text-success"></i></label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <h2 class="fs-5">{{ __('Show last year') }}</h2>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input my-2" type="radio" id="filters-last-year-0" name="filters[show-last-year]" value="0" @if (request('filters.show-last-year') !== '1') checked @endif>
                                <label class="form-check-label fs-4" for="filters-last-year-0"><i class="bi bi-x-lg text-danger"></i></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input my-2" type="radio" id="filters-last-year-1" name="filters[show-last-year]" value="1" @if (request('filters.show-last-year') === '1') checked @endif>
                                <label class="form-check-label fs-4" for="filters-last-year-1"><i class="bi bi-check-lg text-success"></i></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row my-4 mx-auto">
            <div class="col table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('School') }}</th>
                            <th scope="col">{{ __('Sector') }}</th>
                            <th scope="col">{{ __('Diploma') }}</th>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Short name') }}</th>
                            <th scope="col">{{ __('Headteacher') }}</th>
                            <th scope="col">{{ __('RNCP') }}</th>
                            <th scope="col">{{ __('Diploma Code') }}</th>
                            <th scope="col">{{ __('Requested graduation') }}</th>
                            <th scope="col">{{ __('Duration') }}</th>
                            <th scope="col">{{ __('Price') }}</th>
                            {{--<th scope="col">{{ __('Training Manager') }}</th>--}}
                            <th scope="col">{{ __('Started at') }}</th>
                            <th scope="col">{{ __('Ended at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trainings as $training)
                            <tr @class([
                                    'table-warning' => is_null($training['certification']),
                                    'small' => $training['plusUtilise'] === 1,
                                    'border-top-1' => $loop->index && $trainings[$loop->index - 1]['plusUtilise'] === 0,
                                ])
                                data-id="{{ $training['codeFormation'] }}"
                            >
                                <td>{{ explode(' ', $training['customData']['School'])[0] }}</td>
                                <td>{{ $training['nomSecteurActivite'] }}</td>
                                <td class="text-nowrap">{{ $training['diplome']['nomDiplome'] }}</td>
                                <td @class([
                                        'text-decoration-line-through' => $training['plusUtilise'] === 1,
                                    ])
                                >{{ $training['nomFormation'] }}</td>
                                <td class="text-nowrap">{{ $training['abregeFormation'] }}</td>
                                <td @class([
                                        'text-nowrap',
                                        'text-muted' => is_null($training['headTeacher']) && !is_null($training['certification']),
                                    ])
                                >{{ $training['headTeacher'] ?? __('N/A') }}</td>
                                <td>
                                    @if ($training['certification'])
                                        {{ $training['certification']['nomenclature'] }}&nbsp;/&nbsp;{{ $training['diplome']['nomenclNiveau'] }}<br>
                                        <small>
                                            {{ $training['customData']['Certificateur'] }}<br>
                                            {{ $training['customData']['Date échéance certification'] }}
                                        </small>
                                    @else
                                        <strong>{{ __('Not RNCP') }}</strong>
                                    @endif
                                </td>
                                <td>{{ $training['codeEn'] }}</td>
                                <td>{{ $training['niveau'] ?? '0' }}</td>
                                <td class="text-lowercase">{{ $training['dureeHeures'] }}&nbsp;{{ __('Hours') }}</td>
                                <td>{{ $training['prixVente'] }}&nbsp;€</td>
                                {{--<td data-id="{{ $group['codePersonnel'] }}">{{ $trainers[$group['codePersonnel']]?->fullname ?? __('N/A') }}</td>--}}
                                {{--<td class="text-nowrap">{{ $group['dateDebut'] }}</td>
                                <td class="text-nowrap">{{ $group['dateFin'] }}</td>--}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
    <script>
        (function () {
            document.getElementById('filters-form')
                    .addEventListener('change', function (ev) {
                        this.submit();
                    });
        })();
    </script>
@endpush
