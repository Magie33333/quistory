<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Výběr Kvízu</title>
</head>
<body>
    <h1>Vyberte kvíz</h1>
    <ul>
    <?php foreach ($kvizy as $kviz): ?>
        <li><a href="KvizProbiha.php?kviz_id=<?php echo $kviz['kviz_id']; ?>"><?php echo $kviz['nazev']; ?></a></li>
    <?php endforeach; ?>
    </ul>
</body>
</html>