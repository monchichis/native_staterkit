<?php
// Ensure this file is only included once
if (!defined('CONNECTION_LOADED')) {
    define('CONNECTION_LOADED', true);

    // Identify path to Database.php
    $dbClassPath = __DIR__ . '/../core/Database.php';
    
    if (file_exists($dbClassPath)) {
        include_once $dbClassPath;
    } else {
        // Fallback or error if core file missing
        die('Core Database class missing.');
    }

    // Configuration
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database_name = 'native_db';

    // Initialize Database Class
    try {
        $db = new Database($host, $username, $password, $database_name);
        $conn = $db->getConnection(); // For backward compatibility
    } catch (Exception $e) {
         // Handle error gracefully or redirect to setup
    }

    // Helper Functions
    if (!function_exists('base_url')) {
        function base_url($uri = '') {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $base_path = rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']), '/');
            $uri = ltrim($uri, '/');
            return $protocol . $host . $base_path . ($uri ? '/' . $uri : '');
        }
    }

    if (!function_exists('checkInternetConnection')) {
        function checkInternetConnection($url = 'https://www.google.com') {
             $timeout = 5; 
             $ch = curl_init($url);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
             $response = curl_exec($ch);
             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
             curl_close($ch);
             return ($httpCode >= 200 && $httpCode < 300);
        }
    }

    if (!function_exists('checkSession')) {
        function checkSession() {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['username'])) {
                header("Location: index.php");
                exit();
            }
        }
    }
}
?>