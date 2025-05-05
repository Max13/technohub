@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Cryptography') }}</h1>
        <p class="fw-light mt-3">{!! __('The utilities on this page are processed in your browser. Nothing is sent to the server except the <abbr title="Random Number Generator">RNG</abbr>.') !!}</p>

        <div class="row mt-5 mb-4">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">{{ __('Random numbers generator') }}</div>
                    <div class="card-body">
                        <form id="rng">
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label for="rng_seed" class="form-label">{{ __('Seed') }}</label>
                                    <input type="number" id="rng_seed" class="form-control" min="{{ $rand_min }}" max="{{ $rand_max }}" step="1" value="{{ old('rng_seed', 0) }}" data-localstore>
                                    <div id="rng_seed_help" class="form-text px-1">0 = {{ __('Random') }}</div>
                                </div>
                                <div class="col">
                                    <label for="rng_min" class="form-label">{{ __('Minimum') }}</label>
                                    <input type="number" id="rng_min" class="form-control" min="{{ $rand_min }}" max="{{ $rand_max }}" step="1" value="{{ old('rng_min', 0) }}" data-localstore>
                                </div>
                                <div class="col">
                                    <label for="rng_max" class="form-label">{{ __('Maximum') }}</label>
                                    <input type="number" id="rng_max" class="form-control" min="{{ $rand_min }}" max="{{ $rand_max }}" step="1" value="{{ old('rng_max', $rand_max) }}" data-localstore>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col">
                                    <label for="rng_draws" class="form-label">{{ __('How many draws?') }}</label>
                                    <input type="number" id="rng_draws" class="form-control" min="1" max="100" step="1" value="{{ old('rng_draws', 10) }}">
                                </div>
                                <div class="col d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">{{ __('Submit') }}</button>
                                </div>
                            </div>
                            <div class="row g-3" id="rng_results"></div>
                        </form>
                        <script>
                            // LocalStorage auto save/load
                            document.addEventListener('DOMContentLoaded', () => {
                                document.querySelectorAll('[data-localstore]')
                                        .forEach(el => {
                                            if (localStorage[el.id] !== undefined) {
                                                el.value = localStorage[el.id];
                                            }

                                            el.addEventListener('change', () => {
                                                localStorage[el.id] = el.value;
                                            });
                                        });
                            });

                            // RNG
                            document.getElementById('rng').addEventListener('submit', e => {
                                e.preventDefault();

                                const seed = document.getElementById('rng_seed').value;
                                const min = document.getElementById('rng_min').value;
                                const max = document.getElementById('rng_max').value;
                                const draws = document.getElementById('rng_draws').value;
                                const resultsDiv = document.getElementById('rng_results');

                                axios.get('{{ route('misc.crypto.getRng') }}?seed=' + seed + '&min=' + min + '&max=' + max + '&draws=' + draws)
                                     .then(function (response) {
                                         const newDiv = document.createElement('div');
                                         const newUl = document.createElement('ul');

                                         newDiv.classList.add('col-auto');
                                         newUl.classList.add('mb-1');
                                         newDiv.appendChild(newUl);

                                         response.data.forEach(function (n) {
                                             const newLi = document.createElement('li');
                                             newLi.innerHTML = n;
                                             newUl.appendChild(newLi);
                                         });

                                         resultsDiv.appendChild(newDiv);
                                     })
                                     .catch(function (error) {
                                         console.log(error);
                                     });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
