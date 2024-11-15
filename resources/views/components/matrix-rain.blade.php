@push('styles')
    <style>
        @font-face {
            font-family: 'Matrix Code';
            src: url(/fonts/matrix-code.otf) format('opentype');
            unicode-range: U+0021-003F, U+0041-005F, U+0061-007E;
        }

        html, body {
            height: 100%;
        }

        #matrix-rain {
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
            width: 100%;
            height: 100%;
        }
    </style>
@endpush
<canvas id="matrix-rain"></canvas>
@push('scripts')
    <script>
        (() => {
            // const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?/あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわをんｦｱｳｴｵｶｷｹｺｻｼｽｾｿﾀﾂﾃﾅﾆﾇﾈﾊﾋﾎﾏﾐﾑﾒﾓﾔﾕﾗﾘﾜ".split('');
            const canvas = document.getElementById('matrix-rain');
            const color = '#0f0';
            const fontSize = 12;
            const letters = 'abcdefghijklmnopqrstuvwxyz0123456789$+-*=%"\'#&_(),.;:?!\\|{}<>[]^~'.split('');

            let ctx = canvas.getContext('2d');
            let columns;
            let drops;

            function draw() {
                ctx.fillStyle = 'rgba(0, 0, 0, .06)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.font = fontSize + 'px "Matrix Code", monospace';

                for (let x = 0; x < drops.length; ++x) {
                    let text = letters[Math.floor(Math.random() * letters.length)];
                    ctx.fillStyle = color;
                    ctx.fillText(text, x * fontSize, drops[x] * fontSize);
                    drops[x]++;
                    if (drops[x] * fontSize > canvas.height && Math.random() > 0.95) {
                        drops[x] = 0;
                    }
                }
            }

            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                columns = canvas.width / fontSize;
                drops = [];
                for (let x = 0; x < columns; ++x) {
                    drops[x] = Math.random() * 100;
                }
            }

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            setInterval(draw, 33);
        })();
    </script>
@endpush
