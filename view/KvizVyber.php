<?php
include '../controller/KvizController.php';
$controller = new KvizController($conn);
$kvizy = $controller->zobrazKvizy(); // Předpokládá se, že tato metoda existuje a získává data kvízů
$uzivatel_id = $_SESSION['uzivatel_id'];
$mozkaky = $controller->ziskatStavMozkaku($uzivatel_id);
$_SESSION['odemceneKvizy'] = $controller->zobrazOdemceneKvizy($uzivatel_id);
include './KvizVyberView.php'; // Předá data kvízů do view
?>