<?php
session_start();
require_once 'admin_functions.php';

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$db = new AdminDatabaseFunctions();
$gebruikersnaam = $_SESSION['gebruikersnaam'];
$games = $db->getAllGames();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $titel = trim($_POST['titel'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $platform = trim($_POST['platform'] ?? '');
    $uitgever = trim($_POST['uitgever'] ?? '');
    $releasejaar = (int)($_POST['releasejaar'] ?? 0);
    $rating = (float)($_POST['rating'] ?? 0);
    $prijs = (float)($_POST['prijs'] ?? 0);
    $beschrijving = trim($_POST['beschrijving'] ?? '');
    
    if (empty($titel)) {
        $error = 'Titel is verplicht';
    } else {
        $result = $db->addGame($titel, $genre, $platform, $uitgever, $releasejaar, $rating, $prijs, $beschrijving);
        if ($result['success']) {
            $message = $result['message'];
            $games = $db->getAllGames();
        } else {
            $error = $result['message'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_game'])) {
    $game_id = (int)($_POST['game_id'] ?? 0);
    $result = $db->deleteGame($game_id);
    if ($result['success']) {
        $message = $result['message'];
        $games = $db->getAllGames();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <p>Ingelogd als: <strong><?= htmlspecialchars($gebruikersnaam) ?></strong> (Administrator)</p>
    <p><a href="logout.php">Uitloggen</a></p>
    
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <hr>
    
    <h2>Game Toevoegen</h2>
    <form method="POST" action="">
        <table>
            <tr>
                <td><label>Titel*:</label></td>
                <td><input type="text" name="titel" required></td>
            </tr>
            <tr>
                <td><label>Genre:</label></td>
                <td><input type="text" name="genre"></td>
            </tr>
            <tr>
                <td><label>Platform:</label></td>
                <td><input type="text" name="platform"></td>
            </tr>
            <tr>
                <td><label>Uitgever:</label></td>
                <td><input type="text" name="uitgever"></td>
            </tr>
            <tr>
                <td><label>Releasejaar:</label></td>
                <td><input type="number" name="releasejaar" min="1970" max="2030"></td>
            </tr>
            <tr>
                <td><label>Rating (0-10):</label></td>
                <td><input type="number" name="rating" step="0.1" min="0" max="10"></td>
            </tr>
            <tr>
                <td><label>Prijs:</label></td>
                <td><input type="number" name="prijs" step="0.01" min="0"></td>
            </tr>
            <tr>
                <td><label>Beschrijving:</label></td>
                <td><textarea name="beschrijving" rows="3"></textarea></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit" name="add_game">Game Toevoegen</button></td>
            </tr>
        </table>
    </form>
    
    <hr>
    
    <h2>Games Beheren</h2>
    <p>Je kunt games toevoegen en verwijderen (DELETE). Je kunt GEEN tabellen verwijderen (DROP).</p>
    
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
                <th>Actie</th>
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
                    <td><?= number_format($game['prijs'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($game['beschrijving']) ?></td>
                    <td>
                        <form method="POST" action="" onsubmit="return confirm('Weet je zeker dat je deze game wilt verwijderen?');">
                            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                            <button type="submit" name="delete_game">Verwijder</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
</body>
</html>