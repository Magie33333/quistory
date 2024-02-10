<?php
session_start();
include "../conf/connect.php";
include "./header.php";


$kviz_id = isset($_GET['kviz_id']) ? $_GET['kviz_id'] : null;

if (!$kviz_id) {
    die("ID kvízu není zadáno.");
}

$vysledky = [];

$sql = "SELECT u.username, v.skore, v.datum_spocteni FROM vysledky v
        JOIN uzivatele u ON v.uzivatel_id = u.id
        WHERE v.kviz_id = :kviz_id
        ORDER BY v.skore DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['kviz_id' => $kviz_id]);
$vysledky = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Výsledky kvízu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>    

    <h1>Výsledky kvízu</h1>

    <table>
        <tr>
            <th>Pořadí</th>
            <th>Uživatel</th>
            <th>Skóre</th>
            <th>Datum</th>
        </tr>
        <?php foreach ($vysledky as $index => $vysledek): ?>
            <tr>
                <td><?php echo ($index + 1); ?></td>
                <td><?php echo htmlspecialchars($vysledek['username']); ?></td>
                <td><?php echo htmlspecialchars($vysledek['skore']); ?></td>
                <td><?php echo htmlspecialchars($vysledek['datum_spocteni']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>