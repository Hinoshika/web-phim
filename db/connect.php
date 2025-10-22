<?php
class connect {
    private static $instance = null;
    private $conn;

    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db = "anime";

    private function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        $this->conn->set_charset("utf8");

        if ($this->conn->connect_error) {
            die("Kết nối thất bại: " . $this->conn->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new connect();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
