<?php
// Deteksi Informasi Web Server
$webServer = $_SERVER['SERVER_SOFTWARE'];
$infoWebServer = strtok($webServer, '/');

// Deteksi Versi PHP
$phpVersion = phpversion();

// Deteksi Alamat IP Publik
$publicIP = file_get_contents('https://api64.ipify.org?format=json');
$publicIP = json_decode($publicIP, true)['ip'];

?>