<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

    $query = new MongoDB\Driver\Query([], ['sort' => ['timestamp' => -1]]);
    $cursor = $manager->executeQuery("bostarter.log_eventi", $query);

    echo "<h2>Log degli Eventi</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Timestamp</th><th>Messaggio</th></tr>";

    foreach ($cursor as $doc) {
        $data = $doc->timestamp->toDateTime()->format('Y-m-d H:i:s');
        $msg = htmlspecialchars($doc->messaggio);
        echo "<tr><td>{$data}</td><td>{$msg}</td></tr>";
    }

    echo "</table>";
} catch (Exception $e) {
    echo "Errore nel recupero dei log: " . $e->getMessage();
}
?>
