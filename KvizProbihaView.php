<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Kvíz</title>
    <!-- Zde můžete přidat styly nebo skripty -->
    <script type="text/javascript">
        function overitVyber() {
            var moznosti = document.querySelectorAll('input[name="moznost_id"]:checked');
            if (moznosti.length == 0) {
                alert('Prosím, vyberte odpověď.');
                return false;
            }
            return true;
        }

        let zbyvajiciCas = <?php echo $_SESSION['zbývajícíCas']; ?>; // 15 sekund

        function odpocetCasu() {
            if (zbyvajiciCas <= 0) {
                // Čas vypršel, přesměrování na ukončení kvízu
                //window.location.href = 'KvizController.php?action=ukoncitKviz';
            } else {
                // Aktualizace zbyvajícího času a zobrazení v UI
                document.getElementById('zbyvajiciCas').textContent = zbyvajiciCas;
                zbyvajiciCas--;
            }
        }

        // Spuštění časovače
        setInterval(odpocetCasu, 1000); // Aktualizace každou sekundu
    </script>
</head>
<body>
    <h1><?php echo $nazevKvizu; ?></h1>

    <form id="kvizFormular" action="KvizController.php" method="post" onsubmit="return overitVyber()">
        <h2><?php echo $otazka['otazka_text']; ?></h2>
        <input type="hidden" name="action" value="zpracujOdpoved">
        <input type="hidden" name="otazka_id" value="<?php echo $otazka['otazka_id']; ?>">
        <input type="hidden" name="kviz_id" value="<?php echo $kviz_id; ?>">
        <?php foreach ($odpovedi as $moznost): ?>
            <div>
                <input type="radio" name="moznost_id" value="<?php echo $moznost['moznost_id']; ?>" id="moznost_<?php echo $moznost['moznost_id']; ?>">
                <label for="moznost_<?php echo $moznost['moznost_id']; ?>"><?php echo $moznost['moznost_text']; ?></label>
            </div>
        <?php endforeach; ?>
        <input type="submit" value="Odpovědět">
    </form>
    <div>Čas do ukončení kvízu: <span id="zbyvajiciCas">15</span> sekund</div>
</body>
</html>