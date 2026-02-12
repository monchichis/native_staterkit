<?php
    // Include connection if not already included
    include_once __DIR__ . '/../connection/connection.php';
    
    // Fungsi untuk mendapatkan jumlah tabel di database
    function countTables($conn, $DbName = null)
    {
        // Use global database name if not provided
        if ($DbName === null) {
            global $database_name;
            $DbName = $database_name;
        }

        // Check if database name is available
        if (empty($DbName)) {
            return 0; 
        }

        $query = "SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = ?";
        
        // Use prepared statement if $conn is a mysqli object
        if ($conn instanceof mysqli) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $DbName);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
               $row = $result->fetch_assoc();
               return $row['total_tables'];
            }
        }
        
        return 0;
    }
?>