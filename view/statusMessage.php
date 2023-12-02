<?php
session_start();

function displayStatusMessage() {
    if (isset($_SESSION['status_message'])) {
        $message = $_SESSION['status_message'];
        $status = $_SESSION['status_type'] ?? 'success'; // Předpokládáme 'success' pokud není zadán jiný typ

        echo "<div class='alert $status'>$message</div>";

        // Odstranění zprávy po zobrazení
        unset($_SESSION['status_message']);
        unset($_SESSION['status_type']);
    }
}
?>