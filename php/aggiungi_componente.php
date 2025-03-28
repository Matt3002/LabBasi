<?php
session_start();
require 'config.php';

$nomeProgetto = $_POST['nome_progetto'] ?? null;
$nomeComponente = $_POST['nome_componente'] ?? null;
$quantita = $_POST['quantita'] ?? 0;

if (!$nomeProgetto || !$nomeComponente || $quantita <= 0) {
    $_SESSION['error'] = "Dati non validi per l'aggiunta.";
    header("Location: inserisci_componenti.php?progetto=" . urlencode($nomeProgetto));
    exit;
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    $_SESSION['error'] = "Errore di connessione al DB.";
    header("Location: inserisci_componenti.php?progetto=" . urlencode($nomeProgetto));
    exit;
}

// Inserisce o aggiorna la componente
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $stmt = $conn->prepare("INSERT INTO Lista_Componenti (nome_Componente, nome_Progetto, quantita)
                            VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE quantita = quantita + VALUES(quantita)");
    $stmt->bind_param("ssi", $nomeComponente, $nomeProgetto, $quantita);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Componente aggiunta con successo.";
} catch (mysqli_sql_exception $e) {
    $_SESSION['error'] = "Errore durante l'aggiunta: " . $e->getMessage();
}

$conn->close();
header("Location: inserisci_componenti.php?progetto=" . urlencode($nomeProgetto));
exit;
?>
