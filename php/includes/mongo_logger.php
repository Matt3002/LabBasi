<?php
function logEvento($messaggio) {
    try {
        $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            "messaggio" => $messaggio,
            "timestamp" => new MongoDB\BSON\UTCDateTime()
        ]);

        $manager->executeBulkWrite("bostarter.log_eventi", $bulk);
    } catch (Exception $e) {
        error_log("Errore MongoDB: " . $e->getMessage());
    }
}
?>