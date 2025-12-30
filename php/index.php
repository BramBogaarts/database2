<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit();
}

// Role check met fallback
$role = $_SESSION['role'] ?? 'user';

if ($role === 'admin') {
    header('Location: admin.php');
    exit();
}

$db = new DatabaseFunctions();
$gebruikersnaam = $_SESSION['gebruikersnaam'];
$games = $db->getAllGames();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Home - Games</title>
</head>
<body>
    <h1>Welkom, <?= htmlspecialchars($gebruikersnaam) ?>!</h1>
    <p>Account type: Normale Gebruiker</p>
    
    <p><a href="logout.php">Uitloggen</a></p>
    
    <hr>
    
    <h2>Games Overzicht</h2>
    <p>Je kunt alleen games bekijken (read-only)</p>
    
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titel</th>
                <th>Genre</th>
                <th>Platform</th>
                <th>Uitgever</th>
                <th>Releasejaar</th>
                <th>Rating</th>
                <th>Prijs</th>
                <th>Beschrijving</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($games as $game): ?>
                <tr>
                    <td><?= $game['id'] ?></td>
                    <td><?= htmlspecialchars($game['titel']) ?></td>
                    <td><?= htmlspecialchars($game['genre']) ?></td>
                    <td><?= htmlspecialchars($game['platform']) ?></td>
                    <td><?= htmlspecialchars($game['uitgever']) ?></td>
                    <td><?= $game['releasejaar'] ?></td>
                    <td><?= $game['rating'] ?></td>
                    <td>€<?= number_format($game['prijs'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($game['beschrijving']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>