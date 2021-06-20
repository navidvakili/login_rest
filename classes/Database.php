<?php
require_once __DIR__ . '/../Config.php';
class Database
{

    // CHANGE THE DB INFO ACCORDING TO YOUR DATABASE
    private $db_host = Config::DB_HOST;
    private $db_name = Config::DB_NAME;
    private $db_username = Config::DB_USERNAME;
    private $db_password = Config::DB_PASSWORD;

    public function dbConnection()
    {

        try {
            $conn = new PDO('mysql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_username, $this->db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            echo "Connection error " . $e->getMessage();
            exit;
        }
    }
}
