<?php
// Funzione per registrare un evento di log in una collezione MongoDB
function logEvento($messaggio) {
    try {
        // Crea una connessione al server MongoDB in locale
        $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

        // Prepara l'operazione di inserimento
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            "messaggio" => $messaggio, // Messaggio da loggare
            "timestamp" => new MongoDB\BSON\UTCDateTime() // Timestamp automatico in UTC
        ]);

        // Esegue l'inserimento nella collezione "log_eventi" del database "bostarter"
        $manager->executeBulkWrite("bostarter.log_eventi", $bulk);
    } catch (Exception $e) {
        // In caso di errore nella connessione o nell'inserimento, scrive l'errore nei log PHP
        error_log("Errore MongoDB: " . $e->getMessage());
    }
}
?>
