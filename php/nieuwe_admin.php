<?php
require_once 'admin_functions.php';
$created = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    
    if (empty($gebruikersnaam) || empty($wachtwoord)) {
        $error = 'Vul alle velden in';
    } elseif (strlen($wachtwoord) < 6) {
        $error = 'Wachtwoord moet minimaal 6 karakters zijn';
    } else {
        $db = new AdminDatabaseFunctions();
        $result = $db->register($gebruikersnaam, $wachtwoord, 'admin');
        
        if ($result['success']) {
            $created = true;
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
    <title>Admin Aanmaken</title>
</head>
<body>

    <?php if ($created): ?>
        <div class="success">
            <h2>✅ Admin Account Aangemaakt!</h2>
            <p>Je kunt nu inloggen via <a href="login.php">de login pagina</a></p>
        </div>
    <?php else: ?>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <h1>Admin Account Aanmaken</h1>
        
        <form method="POST">
            <div>
                <label>Admin Gebruikersnaam:</label><br>
                <input type="text" name="gebruikersnaam" required>
            </div>
            <br>
            <div>
                <label>Admin Wachtwoord:</label><br>
                <input type="password" name="wachtwoord" required minlength="6">
            </div>
            <br>
            <button type="submit">Maak Admin Account</button>
        </form>
    <?php endif; ?>
</body>
</html>