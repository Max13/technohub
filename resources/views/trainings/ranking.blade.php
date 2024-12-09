<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Ranking') }}</title>

    <style>
        @import url(https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap);
        body{
            background-color:#000;
            color:#0f0;
            font-family:'Roboto Mono',monospace;
            margin:0;
            padding:20px;
            text-align:center
        }
        header h1{
            text-shadow:0 0 10px #0f0
        }
        .name{
            font-weight:700;
            font-size:2em
        }
        table{
            margin:0 auto;
            font-size:28px;
            border-spacing:20px;
            border-collapse:separate
        }
        #scoreTable{
            width:70%;
            border-collapse:separate
        }
        #scoreTable td{
            padding:12px;
            text-align:center
        }

        .large{
            float: left;
        }

        .score{
            float: right;
        }

        .scrolling-cell {
            position: relative;
            overflow: hidden;
            height: 30px; /* Ajustez selon la hauteur du texte */
            font-family: monospace; /* Assurez-vous d'avoir un espacement de caractères régulier */
        }

        .scrolling-text {
            position: absolute;
            bottom: 0;
            display: flex;
            flex-direction: column;
        }

        .scrolling-letter {
            opacity: 0;
            animation: scrollText 3s infinite;
        }

        @keyframes scrollText {
            0% {
                opacity: 0;
                transform: translateY(100%);
            }
            50% {
                opacity: 1;
                transform: translateY(0); /* Position finale */
            }
            100% {
                opacity: 0;
                transform: translateY(-100%);
            }
        }
    </style>
</head>
<body>
<header>
    <h1>{{ __('Ranking') }} {{ $training->name }}</h1>
</header>
<table id="scoreTable">
    <tbody></tbody>
</table>
<script>
    function loadData() {
        const data = {!! $training->students->toJson() !!};
        const parsedData = data.map(user => {
            return {
                name: user.firstname + ' ' + user.lastname[0] + '.',
                points: user.total_points,
            }
        }).sort((u1, u2) => u2.points - u1.points);

        console.log("$$$$$", parsedData);

        const tableBody = document.querySelector("#scoreTable tbody")
        parsedData.forEach((entry, i) => {
            const tr = document.createElement("tr")
            tr.innerHTML = `
    <td class="large" data-final="${entry.name}" data-type="name"></td>
    <td class="score" data-final="${entry.points}" data-type="score"></td>
  `
            tableBody.appendChild(tr)
        })

        startAnim()
    }

    function getRandomText(length, characters) {
        return Array.from(
            { length },
            () => characters[Math.floor(Math.random() * characters.length)]
        )
    }

    function revealText(
        cell,
        randomArray,
        finalValue,
        intervalId,
        finalValueArray,
        finalPosArray,
        characters
    ) {
        const randomIndex =
            finalPosArray[Math.floor(Math.random() * finalPosArray.length)]
        randomArray[randomIndex] =
            characters[Math.floor(Math.random() * characters.length)]
        cell.textContent = randomArray.join("")

        const index = finalPosArray.indexOf(randomIndex)
        if (randomArray[randomIndex] !== finalValueArray[randomIndex]) {
            randomArray[randomIndex] = finalValueArray[randomIndex]
            finalPosArray.splice(index, 1)
            cell.textContent = randomArray.join("")
        }

        if (cell.textContent === finalValue) {
            clearInterval(intervalId)
            cell.textContent = finalValue
        }
    }

    function randomText(
        cell,
        randomArray,
        finalValue,
        finalValueArray,
        intervalId,
        characters,
        size
    ) {
        const randomIndex = Math.floor(Math.random() * size)
        if (randomArray[randomIndex] !== finalValueArray[randomIndex]) {
            randomArray[randomIndex] =
                characters[Math.floor(Math.random() * characters.length)]
            cell.textContent = randomArray.join("")
        }

        if (cell.textContent === finalValue) {
            clearInterval(intervalId)
            cell.textContent = finalValue
        }
    }

    function startAnim() {
        const cells = document.querySelectorAll("td[data-type]")
        const characters =
            "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*/あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわをんｦｱｳｴｵカキケコサシスセソタツテナニヌネハヒホマミムメモヤユラリワ"

        cells.forEach((cell) => {
            const finalValue = cell.getAttribute("data-final")
            const type = cell.getAttribute("data-type")
            const size = type === "score" ? 5 : 20
            const randomArray = getRandomText(size, characters)
            const finalValueArray = Array.from(finalValue)
            const finalPosArray = Array.from(
                { length: finalValue.length },
                (_, index) => index
            )

            const deleteLastValue = setInterval(() => {
                randomArray.splice(randomArray.length - 1, 1)
                if (randomArray.length === finalValue.length)
                    clearInterval(deleteLastValue)
            }, 500)

            const revealId = setInterval(() => {
                revealText(
                    cell,
                    randomArray,
                    finalValue,
                    revealId,
                    finalValueArray,
                    finalPosArray,
                    characters
                )
            }, 600)

            const randomId = setInterval(() => {
                randomText(
                    cell,
                    randomArray,
                    finalValue,
                    finalValueArray,
                    randomId,
                    characters,
                    size
                )
            }, 100)
        })
    }

    window.onload = loadData
</script>
</body>
</html>
