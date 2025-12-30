<?php
class DatabaseFunctions
{
    private $conn;

    public function __construct()
    {
        $this->loadEnv();

        // EERST: Setup met ROOT
        $this->setupDatabase();

        // DAN: Connecteer met normal_user
        $host = $_ENV['DB_HOST'];
        $db = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER_USERNAME'];
        $pass = $_ENV['DB_USER_PASSWORD'];

        $this->conn = new mysqli($host, $user, $pass, $db);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    private function setupDatabase()
    {
        $root_conn = new mysqli($_ENV['DB_HOST'], 'root', 'root');
        
        if ($root_conn->connect_error) {
            die("Root connection failed: " . $root_conn->connect_error);
        }

        $db = $_ENV['DB_NAME'];
        $admin_user = $_ENV['DB_ADMIN_USERNAME'];
        $admin_pass = $_ENV['DB_ADMIN_PASSWORD'];
        $normal_user = $_ENV['DB_USER_USERNAME'];
        $normal_pass = $_ENV['DB_USER_PASSWORD'];

        $root_conn->query("CREATE DATABASE IF NOT EXISTS `$db`");
        $root_conn->select_db($db);

        // Maak SQL users
        $result = $root_conn->query("SELECT User FROM mysql.user WHERE User = '$admin_user'");
        if ($result->num_rows == 0) {
            $root_conn->query("CREATE USER '$admin_user'@'localhost' IDENTIFIED BY '$admin_pass'");
            $root_conn->query("GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES, 
                              CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW, 
                              SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, TRIGGER 
                              ON `$db`.* TO '$admin_user'@'localhost'");
        }

        $result = $root_conn->query("SELECT User FROM mysql.user WHERE User = '$normal_user'");
        if ($result->num_rows == 0) {
            $root_conn->query("CREATE USER '$normal_user'@'localhost' IDENTIFIED BY '$normal_pass'");
            // Geef normal_user ook EXECUTE rechten
            $root_conn->query("GRANT SELECT, EXECUTE ON `$db`.* TO '$normal_user'@'localhost'");
        }

        $root_conn->query("FLUSH PRIVILEGES");

        // Maak tabellen
        $sql = "CREATE TABLE IF NOT EXISTS gebruikers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            gebruikersnaam VARCHAR(30) NOT NULL UNIQUE,
            wachtwoord VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user'
        )";
        $root_conn->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS games (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            titel VARCHAR(100) NOT NULL,
            genre VARCHAR(50),
            platform VARCHAR(50),
            uitgever VARCHAR(100),
            releasejaar INT,
            rating DECIMAL(3, 1),
            prijs DECIMAL(10, 2),
            beschrijving TEXT
        )";
        $root_conn->query($sql);

        // Voeg sample games toe
        $result = $root_conn->query("SELECT COUNT(*) as count FROM games");
        $row = $result->fetch_assoc();
        if ($row['count'] == 0) {
            $games = [
                ['The Legend of Zelda: Breath of the Wild', 'Action-Adventure', 'Nintendo Switch', 'Nintendo', 2017, 9.7, 59.99, 'Epische open wereld adventure game'],
                ['Red Dead Redemption 2', 'Action-Adventure', 'Multi-platform', 'Rockstar Games', 2018, 9.5, 49.99, 'Western verhaal in open wereld'],
                ['Elden Ring', 'RPG', 'Multi-platform', 'FromSoftware', 2022, 9.3, 59.99, 'Dark fantasy action RPG'],
                ['Minecraft', 'Sandbox', 'Multi-platform', 'Mojang', 2011, 9.0, 26.95, 'Creatieve sandbox survival game']
            ];

            $stmt = $root_conn->prepare("INSERT INTO games (titel, genre, platform, uitgever, releasejaar, rating, prijs, beschrijving) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($games as $game) {
                $stmt->bind_param("ssssidds", $game[0], $game[1], $game[2], $game[3], $game[4], $game[5], $game[6], $game[7]);
                $stmt->execute();
            }
        }

        // Maak procedures
        $root_conn->query("DROP PROCEDURE IF EXISTS register_user");
        $sql = "CREATE PROCEDURE register_user(IN user VARCHAR(30), IN pass VARCHAR(255))
                BEGIN
                    INSERT INTO gebruikers (gebruikersnaam, wachtwoord) VALUES (user, pass);
                END";
        $root_conn->query($sql);

        $root_conn->query("DROP PROCEDURE IF EXISTS login_user");
        $sql = "CREATE PROCEDURE login_user(IN user VARCHAR(30))
                BEGIN
                    SELECT gebruikersnaam, wachtwoord, role FROM gebruikers WHERE gebruikersnaam = user;
                END";
        $root_conn->query($sql);

        $root_conn->close();
    }

    private function loadEnv()
    {
        $envFile = __DIR__ . '/.env';

        if (!file_exists($envFile)) {
            die("Error: .env bestand niet gevonden op: " . __DIR__);
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }

        $required = ['DB_HOST', 'DB_NAME', 'DB_USER_USERNAME', 'DB_USER_PASSWORD', 'DB_ADMIN_USERNAME', 'DB_ADMIN_PASSWORD'];
        foreach ($required as $key) {
            if (!isset($_ENV[$key])) {
                die("Error: $key niet gevonden in .env bestand!");
            }
        }
    }

    public function register($gebruikersnaam, $wachtwoord)
    {
        $hashedPassword = password_hash($wachtwoord, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("CALL register_user(?, ?)");
        $stmt->bind_param("ss", $gebruikersnaam, $hashedPassword);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Registratie succesvol"];
        }
        return ["success" => false, "message" => "Gebruikersnaam bestaat al"];
    }

    public function login($gebruikersnaam, $wachtwoord)
    {
        $stmt = $this->conn->prepare("CALL login_user(?)");
        $stmt->bind_param("s", $gebruikersnaam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($wachtwoord, $row['wachtwoord'])) {
                return [
                    "success" => true,
                    "message" => "Login succesvol",
                    "role" => $row['role'] ?? 'user'
                ];
            }
        }
        return ["success" => false, "message" => "Ongeldige login"];
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
}
?>