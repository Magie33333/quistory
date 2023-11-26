<?php
include 'KvizController.php';
$controller = new KvizController($conn);
$kviz_id = $_GET['kviz_id']; // Předpokládá se, že kviz_id je předáno v URL
$controller->spustKviz($kviz_id);
?>