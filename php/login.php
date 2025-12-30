<?php
session_start();
require_once 'functions.php';

$error = '';
$db = new DatabaseFunctions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    
    if (empty($gebruikersnaam) || empty($wachtwoord)) {
        $error = 'Vul alle velden in';
    } else {
        $result = $db->login($gebruikersnaam, $wachtwoord);
        
        if ($result['success']) {
            $_SESSION['gebruikersnaam'] = $gebruikersnaam;
            $_SESSION['role'] = $result['role']; // BELANGRIJK: Zet role in sessie
            $_SESSION['login_time'] = time();
            header('Location: index.php');
            exit();
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
    <title>Inloggen</title>
</head>
<body>
    <h1>Inloggen</h1>
    
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>Gebruikersnaam:</label><br>
            <input type="text" name="gebruikersnaam" required>
        </div>
        <br>
        <div>
            <label>Wachtwoord:</label><br>
            <input type="password" name="wachtwoord" required>
        </div>
        <br>
        <button type="submit">Inloggen</button>
    </form>
    
    <p>Nog geen account? <a href="nieuwegebruiker.php">Registreer hier</a></p>
</body>
</html>