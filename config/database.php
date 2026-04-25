<?php
/**
 * Database Configuration
 *
 * Single PDO connection to food_ordering_db.
 * The class is named `Database` so every existing require/new Database() call
 * continues to work unchanged.
 */
class Database
{
    private string $host     = 'localhost';
    private string $db_name  = 'food_ordering_db';
    private string $username = 'root';
    private string $password = '';

    /** @var PDO|null  singleton connection */
    private ?PDO $conn = null;

    public function getConnection(): PDO
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,   // true prepared statements
            ]);
        } catch (PDOException $e) {
            error_log('[Database] Connection failed: ' . $e->getMessage());
            die('<b>Database error:</b> Could not connect to food_ordering_db. Check config/database.php.');
        }

        return $this->conn;
    }
}
