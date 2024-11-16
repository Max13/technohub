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
            const message = '{{ $message }}';
            let messagePos = [];

            let ctx = canvas.getContext('2d');
            let columns;
            let drops;

            function draw() {
                ctx.fillStyle = 'rgba(0, 0, 0, .06)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // ctx.font = fontSize + 'px "Matrix Code", monospace';

                for (let x = 0; x < drops.length; ++x) {
                    let textX = Math.round(x * fontSize);
                    let textY = Math.round(drops[x] * fontSize);
                    let char = letters[Math.floor(Math.random() * letters.length)];

                    ctx.font = fontSize + 'px "Matrix Code", monospace';
                    ctx.fillStyle = color;

                    // if (
                    //        x >= remoteAddrPos.x
                    //     && x < remoteAddrPos.x + remoteAddr.length
                    //     && textY >= (remoteAddrPos.y - fontSize) / 2
                    //     && textY <= (remoteAddrPos.y + fontSize) / 2
                    // ) {
                    //     ctx.font = fontSize + 'px monospace';
                    //     char = remoteAddr[x - remoteAddrPos.x];
                    // } else {
                    //     ctx.font = fontSize + 'px "Matrix Code", monospace';
                    // }

                    ctx.fillText(char, textX, textY);
                    drops[x]++;
                    if (drops[x] * fontSize > canvas.height && Math.random() > 0.95) {
                        drops[x] = 0;
                    }
                }

                // ctx.font = fontSize + 'px monospace';
                // ctx.fillText(remoteAddr, remoteAddrPos[0] * fontSize, remoteAddrPos[1] * fontSize);
            }

            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                columns = canvas.width / fontSize;
                drops = [];

                for (let x = 0; x < columns; ++x) {
                    drops[x] = Math.random() * 100;
                }

                // remoteAddrPos.x = Math.round((columns / 2) - (remoteAddr.length / 2));
                // remoteAddrPos.y = Math.round(canvas.height / fontSize) - 3;
                // console.log('remoteAddrPos', remoteAddrPos, drops.length);
                message.split('').forEach((c, i) => {
                    messagePos.push({
                        x: (canvas.width / 2) - (message.length * fontSize / 2) + fontSize * i,
                        y: canvas.height - (canvas.height / fontSize - 2),
                    });
                });
            }

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            // Draw digital rain
            setInterval(draw, 33);

            // Show visitor's IP address
            setInterval(() => {
                const i = Math.floor(Math.random() * message.length);
                ctx.font = (fontSize * 1.5) + 'px monospace';
                ctx.fillText(message[i], messagePos[i].x, messagePos[i].y);
            }, 20);
        })();
    </script>
@endpush
