<?php
/**
 * Database Wrapper Class
 * Compatible with PHP 7.4 - 8.2
 * Uses mysqli with prepared statements for security.
 */

class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;

    public function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        $this->connect();
    }

    private function connect() {
        try {
            // Enable error reporting for mysqli
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            $this->connection->set_charset("utf8mb4");

        } catch (mysqli_sql_exception $e) {
            // Throw exception instead of dying, so caller can handle it
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute a query with optional parameters (Prepared Statement)
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt === false) {
                 throw new Exception("Prepare failed: " . $this->connection->error);
            }

            if (!empty($params)) {
                $types = "";
                $values = [];

                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= "i";
                    } elseif (is_float($param)) {
                        $types .= "d";
                    } elseif (is_string($param)) {
                        $types .= "s";
                    } else {
                        $types .= "b"; // Blob
                    }
                    $values[] = $param;
                }
                
                // Variadic bind_param (PHP 5.6+)
                $stmt->bind_param($types, ...$values);
            }

            $stmt->execute();
            return $stmt;

        } catch (Exception $e) {
            die("Query Error: " . $e->getMessage());
        }
    }

    /**
     * Fetch all rows as an associative array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    /**
     * Fetch a single row as an associative array
     */
    public function fetchRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    /**
     * Insert data and return the last inserted ID
     */
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $params = array_values($data);
        
        $stmt = $this->query($sql, $params);
        $insertId = $this->connection->insert_id;
        $stmt->close();
        
        return $insertId;
    }

    /**
     * Update data in the database
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = "";
        $params = [];
        
        foreach ($data as $col => $val) {
            $set .= "$col = ?, ";
            $params[] = $val;
        }
        $set = rtrim($set, ", ");
        
        $sql = "UPDATE $table SET $set WHERE $where";
        $params = array_merge($params, $whereParams);
        
        $stmt = $this->query($sql, $params);
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        return $affectedRows;
    }

    /**
     * Delete data from the database
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        return $affectedRows;
    }
    
    /**
    * Escape string manually (Use sparingly, prefer prepared statements)
    */
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
}
