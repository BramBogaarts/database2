<?php
session_start();
require_once 'functions.php';



$error = '';
$success = '';
$db = new DatabaseFunctions();

// Verwerk registratie formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    $wachtwoord_confirm = $_POST['wachtwoord_confirm'] ?? '';
    
    if (empty($gebruikersnaam) || empty($wachtwoord) || empty($wachtwoord_confirm)) {
        $error = 'Vul alle velden in';
    } elseif ($wachtwoord !== $wachtwoord_confirm) {
        $error = 'Wachtwoorden komen niet overeen';
    } elseif (strlen($wachtwoord) < 6) {
        $error = 'Wachtwoord moet minimaal 6 karakters zijn';
    } else {
        $result = $db->register($gebruikersnaam, $wachtwoord);
        
        if ($result['success']) {
            $success = $result['message'] . ' - Je kunt nu inloggen!';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren</title>
</head>
<body>
    <h1>Registreren</h1>
    
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <p style="color: green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div>
            <label for="gebruikersnaam">Gebruikersnaam:</label><br>
            <input type="text" id="gebruikersnaam" name="gebruikersnaam" required>
        </div>
        <br>
        
        <div>
            <label for="wachtwoord">Wachtwoord:</label><br>
            <input type="password" id="wachtwoord" name="wachtwoord" required minlength="6">
        </div>
        <br>
        
        <div>
            <label for="wachtwoord_confirm">Bevestig Wachtwoord:</label><br>
            <input type="password" id="wachtwoord_confirm" name="wachtwoord_confirm" required minlength="6">
        </div>
        <br>
        
        <button type="submit">Registreren</button>
    </form>
    
    <p>Al een account? <a href="login.php">Log hier in</a></p>
</body>
</html>