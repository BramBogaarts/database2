<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

class AdminDatabaseFunctions
{
    private $conn;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $host = $_ENV['DB_HOST'];
        $db = $_ENV['DB_NAME'];
        $user = $_ENV['DB_ADMIN_USERNAME'];
        $pass = $_ENV['DB_ADMIN_PASSWORD'];

        $this->conn = new mysqli($host, $user, $pass, $db);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getAllGames()
    {
        $sql = "SELECT * FROM games ORDER BY rating DESC";
        $result = $this->conn->query($sql);

        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }

        return $games;
    }

    public function addGame($titel, $genre, $platform, $uitgever, $releasejaar, $rating, $prijs, $beschrijving)
    {
        $stmt = $this->conn->prepare("INSERT INTO games (titel, genre, platform, uitgever, releasejaar, rating, prijs, beschrijving) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssidds", $titel, $genre, $platform, $uitgever, $releasejaar, $rating, $prijs, $beschrijving);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Game succesvol toegevoegd"];
        }
        return ["success" => false, "message" => "Error: " . $this->conn->error];
    }

    public function deleteGame($game_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM games WHERE id = ?");
        $stmt->bind_param("i", $game_id);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Game succesvol verwijderd"];
        }
        return ["success" => false, "message" => "Error: " . $this->conn->error];
    }

    public function register($gebruikersnaam, $wachtwoord, $role = 'user')
    {
        $hashedPassword = password_hash($wachtwoord, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("INSERT INTO gebruikers (gebruikersnaam, wachtwoord, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $gebruikersnaam, $hashedPassword, $role);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Registratie succesvol"];
        }
        return ["success" => false, "message" => "Gebruikersnaam bestaat al"];
    }

    public function login($gebruikersnaam, $wachtwoord)
    {
        $stmt = $this->conn->prepare("SELECT gebruikersnaam, wachtwoord, role FROM gebruikers WHERE gebruikersnaam = ?");
        $stmt->bind_param("s", $gebruikersnaam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($wachtwoord, $row['wachtwoord'])) {
                return [
                    "success" => true,
                    "message" => "Login succesvol",
                    "role" => $row['role']
                ];
            }
        }
        return ["success" => false, "message" => "Ongeldige login"];
    }
}
?>