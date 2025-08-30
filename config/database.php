<?php
// Database Configuration
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "portfolio_db";
    private $conn;

    // Database Connection
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        
        return $this->conn;
    }

    // Get connection instance
    public function getConnection() {
        return $this->connect();
    }
}

// Create database instance
$database = new Database();
$pdo = $database->getConnection();

// Function to execute queries safely
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

// Function to fetch all records
function fetchAll($pdo, $sql, $params = []) {
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

// Function to fetch single record
function fetchOne($pdo, $sql, $params = []) {
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

// Function to get last insert ID
function getLastInsertId($pdo) {
    return $pdo->lastInsertId();
}
?>
