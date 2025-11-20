<?php
class functions
{
    private $conn;
    public function conn()
    {
        $host = "";
        $gebruikersnaam = "";
        $wachtwoord = "";
        $conn = new mysqli($host, $gebruikersnaam, $wachtwoord);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }


        $sql = "CREATE DATABASE ";
        if ($conn->query($sql) === TRUE) {
            echo "Database created successfully";
        } else {
            echo "Error creating database: " . $conn->error;
        }
        $sql = "CREATE TABLE gebruikers (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        gebruikersnaam VARCHAR(30) NOT NULL,
        wachtwoord VARCHAR(30) NOT NULL
)";
        $conn->close();
    }
    public function CreateRegisterProcedure()
    {
        "CREATE PROCEDURE register
AS
INSERT INTO gebruikers (gebruikersnaam, wachtwoord) 
VALUES ('?', '?');
GO";
    }
    public function CreateLoginProcedure()
    {
        "CREATE PROCEDURE login
        AS
        SELECT  gebruikersnaam, wachtwoord FROM gebruikers 
GO";
    }
    public function register(string $gebruikersnaam, string $wachtwoord) {
        "CALL register(gebruikersnaam, wachtwoord)";
    }

    public function login(string $gebruikersnaam, string $wachtwoord) {
         "CALL login(gebruikersnaam)";
    }
}
