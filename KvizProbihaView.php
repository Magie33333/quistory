<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Kvíz</title>
    <!-- Zde můžete přidat styly nebo skripty -->
</head>
<body>
    <h1><?php echo $nazevKvizu; ?></h1>

    <form action="KvizController.php" method="post">
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
</body>
</html>