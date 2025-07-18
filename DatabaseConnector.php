<?php
class DatabaseConnector {
    private $host = "localhost";
    private $dbname = "typetest";
    private $username = "root";
    private $password = "";
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "connect is successful";
        } catch (PDOException $e) {
            die("problem to connect to database" . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
