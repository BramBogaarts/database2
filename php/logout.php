<?php
session_start();

// Verwijder alle sessie variabelen
$_SESSION = array();

// Vernietig de sessie
session_destroy();

// Redirect naar login
header('Location: login.php');
exit();
?>

