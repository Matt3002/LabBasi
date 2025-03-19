<?php
require 'config.php';

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($tables) {
    echo "Connessione a MySQL riuscita! Tabelle esistenti: " . implode(", ", $tables);
} else {
    echo "Connessione MySQL riuscita, ma nessuna tabella trovata.";
}
?>
