<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Výběr Kvízu</title>
</head>
<body>
    <?php
    include 'connect.php'; // Zajistěte, že tento soubor správně vytváří proměnnou $conn
    include 'KvizController.php';

    $controller = new KvizController($conn);
    $kvizy = $controller->zobrazKvizy();
    ?>

    <h1>Vyberte kvíz</h1>
    <ul>
    <?php foreach ($kvizy as $kviz): ?>
        <li><a href="KvizProbihaView.php?kviz_id=<?php echo $kviz['kviz_id']; ?>"><?php echo $kviz['nazev']; ?></a></li>
    <?php endforeach; ?>
    </ul>
</body>
</html>