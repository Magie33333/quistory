<?php
session_unset(); // Vymaže všechny session proměnné
session_destroy(); // Zničí session
header('Location: ../index.php');
exit;
?>