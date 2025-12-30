<?php
class DatabaseFunctions
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "root");
        $db = "databasep2";
        
        // Eerst verbinding maken ZONDER database te selecteren
       
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        // Database aanmaken
        $sql = "CREATE DATABASE IF NOT EXISTS `$db`";
        $this->conn->query($sql);
        $this->conn->select_db($db);
        
        // Tabel aanmaken
        $sql = "CREATE TABLE IF NOT EXISTS gebruikers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            gebruikersnaam VARCHAR(30) NOT NULL UNIQUE,
            wachtwoord VARCHAR(255) NOT NULL
        )";
        
        $this->createProcedures();
        return $this->conn;
    }

    private function createProcedures()
    {
        $this->conn->query("DROP PROCEDURE IF EXISTS register_user");
        $this->conn->query("DROP PROCEDURE IF EXISTS login_user");

        $sql = "CREATE PROCEDURE register_user(IN user VARCHAR(30), IN pass VARCHAR(255))
                BEGIN
                    INSERT INTO gebruikers (gebruikersnaam, wachtwoord) VALUES (user, pass);
                END";
        
        if (!$this->conn->query($sql)) {
            die("Error creating register_user procedure: " . $this->conn->error);
        }

        $sql = "CREATE PROCEDURE login_user(IN user VARCHAR(30))
                BEGIN
                    SELECT gebruikersnaam, wachtwoord FROM gebruikers WHERE gebruikersnaam = user;
                END";
        
        if (!$this->conn->query($sql)) {
            die("Error creating login_user procedure: " . $this->conn->error);
        }
    }

    public function register($gebruikersnaam, $wachtwoord)
    {
        // Hash het wachtwoord
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
            // Gebruik password_verify voor gehashte wachtwoorden
            if (password_verify($wachtwoord, $row['wachtwoord'])) {
                return ["success" => true, "message" => "Login succesvol"];
            }
        }
        return ["success" => false, "message" => "Ongeldige login"];     
    }
}
?>