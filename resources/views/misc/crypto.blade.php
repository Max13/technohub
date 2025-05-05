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

        <div class="row my-4">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">{{ __('Hash function') }}</div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label for="digest_algorithm" class="form-label">{{ __('Algorithm') }}</label>
                                <select class="form-select" id="digest_algorithm" aria-describedby="digest_algorithm_help" data-localstore>
                                    <option value="MD5" data-output-length="128" data-block-size="512" data-safe="false">MD5 / 128 / 512 ⚠</option>
                                    <option value="SHA-1" data-output-length="160" data-block-size="512" data-safe="false">SHA-1 / 160 / 512 ⚠</option>
                                    <option value="SHA-256" data-output-length="256" data-block-size="512" data-safe="true">SHA-256 / 256 / 512</option>
                                    <option value="SHA-384" data-output-length="384" data-block-size="1024" data-safe="true">SHA-384 / 384 / 1024</option>
                                    <option value="SHA-512" data-output-length="512" data-block-size="1024" data-safe="true">SHA-512 / 512 / 1024</option>
                                </select>
                                <div id="digest_algorithm_help" class="form-text"></div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="digest_text" class="form-label">{{ __('Text to hash') }}</label>
                                <textarea class="form-control" id="digest_text"></textarea>
                                <div class="row mt-3 align-items-center">
                                    <div class="col-auto">
                                        <button type="button" id="digest_text_btn" class="btn btn-primary">{{ __('Submit') }}</button>
                                    </div>
                                    <div class="col" id="digest_text_result"></div>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="digest_file" class="form-label">{{ __('File to hash') }}</label>
                                <input class="form-control" type="file" id="digest_file">
                                <div class="row mt-3 align-items-center">
                                    <div class="col-auto">
                                        <button type="button" id="digest_file_btn" class="btn btn-primary">{{ __('Submit') }}</button>
                                    </div>
                                    <div class="col" id="digest_file_result"></div>
                                </div>
                            </div>
                        </form>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                // Digest - Algorithm
                                const md5 = function(d){var r = M(V(Y(X(d),8*d.length)));return r.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_}
                                const digestAlgorithm = document.getElementById('digest_algorithm');
                                let selectedDigestAlgorithm = null;

                                digestAlgorithm.addEventListener('change', ev => {
                                    const helpText = document.getElementById('digest_algorithm_help');

                                    selectedDigestAlgorithm = Array.from(ev.target.options).find(option => option.selected);

                                    helpText.innerHTML =
                                        '{{ __('Raw hash length:') }}&nbsp;' + selectedDigestAlgorithm.dataset.outputLength + ' bits, '
                                      + '{{ __('Block size:') }}&nbsp;' + selectedDigestAlgorithm.dataset.blockSize + ' bits';

                                    if (selectedDigestAlgorithm.dataset.safe === 'false') {
                                        helpText.innerHTML += '<br><span class="text-warning-emphasis">⚠&nbsp;{{ __('Not cryptographically secured') }}</span>';
                                    }
                                });
                                digestAlgorithm.dispatchEvent(new InputEvent('change', {
                                    view: window,
                                    bubbles: true,
                                    cancelable: true,
                                }))

                                // Digest - Text
                                const digestTextArea = document.getElementById('digest_text');
                                const digestTextBtn = document.getElementById('digest_text_btn');
                                const digestTextResult = document.getElementById('digest_text_result');

                                digestTextArea.addEventListener('input', () => {
                                    digestTextResult.innerHTML = '';
                                });

                                digestTextBtn.addEventListener('click', async () => {
                                    let result;

                                    if (selectedDigestAlgorithm.value === 'MD5') {
                                        result = md5(digestTextArea.value);
                                    } else {
                                        result = Array.from(new Uint8Array(await crypto.digest(selectedDigestAlgorithm.value, (new TextEncoder).encode(digestTextArea.value))))
                                                      .map(b => b.toString(16).padStart(2, '0'))
                                                      .join('');
                                    }

                                    digestTextResult.innerHTML = '{{ __('Hash:') }}&nbsp;<code>' + result + '</code>';
                                });

                                // Digest - File
                                const digestFile = document.getElementById('digest_file');
                                const digestFileBtn = document.getElementById('digest_file_btn');
                                const digestFileResult = document.getElementById('digest_file_result');

                                digestFile.addEventListener('change', () => {
                                    digestFileResult.innerHTML = '';
                                });

                                digestFileBtn.addEventListener('click', async () => {
                                    let result;

                                    if (selectedDigestAlgorithm.value === 'MD5') {
                                        // const buf = await digestFile.files[0].arrayBuffer();
                                        // const data = (new TextDecoder()).decode(buf);
                                        // result = md5(data);
                                        result = 'Non fonctionnel pour MD5';
                                    } else {
                                        result = Array.from(new Uint8Array(await crypto.digest(selectedDigestAlgorithm.value, await digestFile.files[0].arrayBuffer())))
                                                      .map(b => b.toString(16).padStart(2, '0'))
                                                      .join('');
                                    }

                                    digestFileResult.innerHTML = '{{ __('Hash:') }}&nbsp;<code>' + result + '</code>';
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
    <script>
        const crypto = window.crypto.subtle;
    </script>
@endpush
