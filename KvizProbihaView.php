<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Kvíz</title>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            let zbyvajiciCas = <?php echo $_SESSION['zbývajícíCas']; ?>;
            const kvizId = <?php echo $kviz_id; ?>;
            nacistPrvniOtazku(kvizId);
            const kvizFormular = document.getElementById('kvizFormular');

            function odpocetCasu() {
                if (zbyvajiciCas <= 0) {
                    ukoncitKviz('Čas kvízu vypršel!');
                } else {
                    document.getElementById('zbyvajiciCas').textContent = zbyvajiciCas;
                    zbyvajiciCas--;
                }
            }

            setInterval(odpocetCasu, 1000);

            kvizFormular.onsubmit = function(event) {
                event.preventDefault();
                if (!overitVyber()) return;

                const otazkaId = document.querySelector('input[name="otazka_id"]').value;
                const moznostId = document.querySelector('input[name="moznost_id"]:checked').value;

                fetch('KvizController.php?action=zpracujOdpoved&otazka_id=' + otazkaId + '&moznost_id=' + moznostId + '&kviz_id=' + kvizId, {
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
            };

            function nacistPrvniOtazku(kvizId) {
                fetch('KvizController.php?action=ziskatDalsiOtazkuAjax&kviz_id=' + kvizId, {
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
                    } else {
                        // Zpracování jiných stavů (expired, completed, atd.)
                    }
                })
                .catch(error => {
                    console.error('Chyba při načítání první otázky: ', error);
                });
            }

            function overitVyber() {
                var moznosti = document.querySelectorAll('input[name="moznost_id"]:checked');
                if (moznosti.length == 0) {
                    alert('Prosím, vyberte odpověď.');
                    return false;
                }
                return true;
            }

            function aktualizovatOtazkuAOdpovedi(otazka, odpovedi) {
                document.getElementById('otazkaText').textContent = otazka.otazka_text;
                const odpovediElem = document.getElementById('odpovedi');
                odpovediElem.innerHTML = '';

                odpovedi.forEach(function(odpoved) {
                    const div = document.createElement('div');
                    const input = document.createElement('input');
                    input.type = 'radio';
                    input.name = 'moznost_id';
                    input.value = odpoved.moznost_id;
                    const label = document.createElement('label');
                    label.appendChild(input);
                    label.appendChild(document.createTextNode(odpoved.moznost_text));
                    div.appendChild(label);
                    odpovediElem.appendChild(div);
                });
            }

            function ukoncitKviz(message) {
                alert(message);
                window.location.href = 'KvizVyber.php';
            }
        });
    </script>
</head>
<body>
    <h1><?php echo $nazevKvizu; ?></h1>

    <form id="kvizFormular" method="post">
        <h2 id="otazkaText"><?php echo $otazka['otazka_text']; ?></h2>
        <input type="hidden" name="action" value="zpracujOdpoved">
        <input type="hidden" name="otazka_id" value="<?php echo $otazka['otazka_id']; ?>">
        <input type="hidden" name="kviz_id" value="<?php echo $kviz_id; ?>">
        <div id="odpovedi">
            <?php foreach ($odpovedi as $moznost): ?>
                <div>
                    <input type="radio" name="moznost_id" value="<?php echo $moznost['moznost_id']; ?>" id="moznost_<?php echo $moznost['moznost_id']; ?>">
                    <label for="moznost_<?php echo $moznost['moznost_id']; ?>"><?php echo $moznost['moznost_text']; ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        <input type="submit" value="Odpovědět">
    </form>
    <div>Čas do ukončení kvízu: <span id="zbyvajiciCas"><?php echo $_SESSION['zbývajícíCas']; ?></span> sekund</div>
</body>
</html>