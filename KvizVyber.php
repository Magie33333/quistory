<?php
include 'KvizController.php';
$controller = new KvizController($conn);
$kvizy = $controller->zobrazKvizy(); // Předpokládá se, že tato metoda existuje a získává data kvízů
include 'KvizVyberView.php'; // Předá data kvízů do view
?>