<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Assicurati che Composer sia installato

use MongoDB\Client;

function logEvento($messaggio, $extra = []) {
    try {
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->bostarter->log_eventi;

        $evento = array_merge([
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'messaggio' => $messaggio
        ], $extra);

        $collection->insertOne($evento);
    } catch (Exception $e) {
        error_log("Errore MongoDB log: " . $e->getMessage());
    }
}
