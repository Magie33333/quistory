<?php
include 'KvizController.php';
$controller = new KvizController($conn);
$kviz_id = $_GET['kviz_id']; // Předpokládá se, že kviz_id je předáno v URL

// Zde by mohlo být případné nastavení session nebo inicializace kvízu
$_SESSION['zacatekKvizu'] = time(); // Uložení současného času jako začátek kvízu
$_SESSION['casovyLimit'] = 60; // 60 sekund pro celý kvíz
$_SESSION['zbývajícíCas'] = 60; // 60 sekund zbývající
$_SESSION['kviz_id'] = $kviz_id; // Uložení ID kvízu
$_SESSION['pocetSpravnych'] = 0; // Reset počtu správných odpovědí
$_SESSION['zobrazenéOtázky'] = []; // Reset zobrazených otázek

// Přesměrování na zobrazení kvízu
include 'KvizProbihaView.php';
?>