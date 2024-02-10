<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Kvíz</title>
    <link rel="stylesheet" href="../css/kviz.css">

       
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

<script src="../scripts/kvizProbiha.js"></script> 
</html>