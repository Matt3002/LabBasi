<?php
// config.php
// Configurazione database MySQL
$host = 'localhost';
$dbname = 'bostarter';
$username = 'root';
$password = 'root'; // Cambia in base alle impostazioni di MAMP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Errore di connessione a MySQL: " . $e->getMessage());
}

// Disabilitato temporaneamente MongoDB
/*
require 'vendor/autoload.php';
$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$logDB = $mongoClient->bostarter_logs;
$eventLogs = $logDB->event_logs;
*/
?>
