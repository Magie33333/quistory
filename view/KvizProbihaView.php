<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Kvíz</title>
    <link rel="stylesheet" href="../css/kviz.css">

    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const kvizId = new URLSearchParams(window.location.search).get('kviz_id');
        const zbyvajiciCasElem = document.getElementById('zbyvajiciCas');
        let zbyvajiciCas = 60; // Přednastavená hodnota, dokud není aktualizována z AJAXu

        if (!kvizId) {
            alert('ID kvízu není zadáno.');
            window.location.href = './KvizVyber.php';
            return;
        }

        // Inicializace kvízu
        nacistPrvniOtazku(kvizId);


        function nacistNazevKvizu(kvizId) {
                fetch('../controller/KvizController.php?action=ziskatNazevKvizu&kviz_id=' + kvizId, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('nazevKvizu').textContent = data.nazev;
                    } else {
                        console.error('Chyba při načítání názvu kvízu: ', data.message);
                    }
                })
                .catch(error => {
                    console.error('Chyba při načítání názvu kvízu: ', error);
                });
        }

        // Přidat volání této funkce na vhodné místo
        nacistNazevKvizu(kvizId);

        const kvizFormular = document.getElementById('kvizFormular');
        kvizFormular.onsubmit = function(event) {
            event.preventDefault();
            odeslatOdpoved();
        };

        function odpocetCasu() {
            if (zbyvajiciCas <= 0) {
                clearInterval(odpocetIntervalu);
                ukoncitKvizCas();
                
            } else {
                if (zbyvajiciCas == 59) { // Hudba se spustí, když začne odpočet
                    spustitHudbu();
                }
                zbyvajiciCasElem.textContent = zbyvajiciCas;
                const total = 282; // Celkový obvod kruhu
                document.getElementById('casovacKruh').style.strokeDashoffset = ((60 - zbyvajiciCas) / 60) * total;
                zbyvajiciCas--;
            }
        }

        let odpocetIntervalu = setInterval(odpocetCasu, 1000);

        function nacistPrvniOtazku(kvizId) {
            fetch('../controller/KvizController.php?action=zahajitKviz&kviz_id=' + kvizId, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.text(); // místo response.json()
                })
                .then(text => {
                    try {
                        return JSON.parse(text); // manuálně zpracujeme text jako JSON
                    } catch (error) {
                        throw new Error('Server did not return JSON: ' + text);
                    }
                })
                .then(data => {
                    if (data.status === 'success') {
                        aktualizovatOtazkuAOdpovedi(data.otazka, data.odpovedi);
                        zbyvajiciCas = data.zbyvajiciCas;
                    } else {
                        ukoncitKviz(data.message);
                    }
                })
                .catch(error => {
                    console.error('Chyba při načítání první otázky: ', error);
                });
        }


        function odeslatOdpoved() {
            const otazkaId = kvizFormular.querySelector('input[name="otazka_id"]').value;
            const moznostId = kvizFormular.querySelector('input[name="moznost_id"]:checked').value;

            fetch('../controller/KvizController.php?action=zpracujOdpoved&otazka_id=' + otazkaId + '&moznost_id=' +
                    moznostId + '&kviz_id=' + kvizId, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        aktualizovatOtazkuAOdpovedi(data.otazka, data.odpovedi);
                        zbyvajiciCas = data.zbyvajiciCas;
                    } else if (data.status === 'expired' || data.status === 'completed') {
                        ukoncitKviz(data.message);
                    }
                })
                .catch(error => {
                    console.error('Chyba při zpracování odpovědi: ', error);
                });
        }

        function aktualizovatOtazkuAOdpovedi(otazka, odpovedi) {
    const otazkaTextElem = document.getElementById('otazkaText');
    otazkaTextElem.textContent = otazka.otazka_text;

    const odpovediElem = document.getElementById('odpovedi');
    odpovediElem.innerHTML = '';

    odpovedi.forEach(function(odpoved) {
        const label = document.createElement('label');
        label.className = "odpoved-container"; // Přidání třídy pro stylování

        const input = document.createElement('input');
        input.type = 'radio';
        input.name = 'moznost_id';
        input.value = odpoved.moznost_id;
        input.id = 'moznost_' + odpoved.moznost_id;

        const span = document.createElement('span');
        span.textContent = odpoved.moznost_text;
        span.className = "odpoved-text"; // Přidání třídy pro stylování

        label.appendChild(input);
        label.appendChild(span); // Vložení textu do span místo přímo do label pro lepší kontrolu stylu
        odpovediElem.appendChild(label);
        });

        kvizFormular.querySelector('input[name="otazka_id"]').value = otazka.otazka_id;
        kvizFormular.querySelector('input[type="submit"]').disabled = false;
        }

        function ukoncitKvizCas() {
        fetch('../controller/KvizController.php?action=ukoncitKviz&kviz_id=' + kvizId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'completed') {
                // Zobrazit výsledky kvízu
                alert(data.message); // Zde můžete přizpůsobit způsob zobrazení výsledků
                window.location.href = './KvizVyber.php';
            }
        })
        .catch(error => {
            console.error('Chyba při ukončování kvízu: ', error);
        });
    }

    function ukoncitKviz($datamessage) {
        alert($datamessage); // Zde můžete přizpůsobit způsob zobrazení výsledků
        window.location.href = './KvizVyber.php';
    }
        
        var hudba = document.getElementById('kvizHudba');
        hudba.volume = 0.5; // Nastaví hlasitost na 50%

        function spustitHudbu() {
            if (hudba.paused) {
                hudba.play().catch(e => {
                    console.log("Audio nelze automaticky přehrát - bude vyžadována interakce uživatele.");
                    // Zde můžete přidat upozornění pro uživatele nebo tlačítko pro spuštění hudby
                });
            }
        }

    });
    </script>

    
</head>

<body>
    <h1 id="nazevKvizu">Načítání kvízu...</h1>

    <form id="kvizFormular">
        <h2 id="otazkaText">Načítání otázky...</h2>
        <div id="odpovedi">
            <!-- Odpovědi budou vloženy sem pomocí JavaScriptu -->
        </div>
        <input type="hidden" name="otazka_id" value="">
        <input type="hidden" name="kviz_id" value="<?php echo htmlspecialchars($kvizId); ?>">
        <div class="form-footer">
            <input type="submit" value="Odpovědět" disabled>
        </div>
    </form>
    <div id="casovacContainer">
        <svg id="casovacSvg" width="100" height="100">
            <circle id="casovacKruh" cx="50" cy="50" r="45" stroke-width="5" stroke="#76b852" fill="transparent" />
        </svg>
        <span id="zbyvajiciCas">60</span>
    </div>

    <audio id="kvizHudba">
    <source src="../conf/audio/FinalChase.mp3" type="audio/mpeg">
    Váš prohlížeč nepodporuje audio element.
</audio>
</body>

</html>