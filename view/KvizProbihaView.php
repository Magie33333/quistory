<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Kvíz</title>
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
                ukoncitKviz('Čas kvízu vypršel!');
                
            } else {
                zbyvajiciCasElem.textContent = zbyvajiciCas;
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
                const div = document.createElement('div');
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'moznost_id';
                input.value = odpoved.moznost_id;
                input.id = 'moznost_' + odpoved.moznost_id;
                const label = document.createElement('label');
                label.htmlFor = input.id;
                label.textContent = odpoved.moznost_text;
                div.appendChild(input);
                div.appendChild(label);
                odpovediElem.appendChild(div);
            });

            kvizFormular.querySelector('input[name="otazka_id"]').value = otazka.otazka_id;
            kvizFormular.querySelector('input[type="submit"]').disabled = false;
        }

        function ukoncitKviz(message) {
            alert(message);
            window.location.href = './KvizVyber.php';
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
        <input type="submit" value="Odpovědět" disabled>
    </form>
    <div>Čas do ukončení kvízu: <span id="zbyvajiciCas">60</span> sekund</div>
</body>

</html>