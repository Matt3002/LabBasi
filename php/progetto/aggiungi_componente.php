<?php
session_start(); // Avvia la sessione per usare $_SESSION
require '../config.php'; // Parametri di connessione al DB
require_once '../includes/mongo_logger.php'; // Logger eventi MongoDB

// Recupera i dati POST dal form
$nomeProgetto = $_POST['nome_progetto'] ?? null;
$nomeComponente = $_POST['nome_componente'] ?? null;
$quantita = $_POST['quantita'] ?? 0;

// Valida i dati ricevuti
if (!$nomeProgetto || !$nomeComponente || $quantita <= 0) {
    $_SESSION['error'] = "Dati non validi per l'aggiunta.";
    header("Location: inserisci_componenti.php?progetto=" . urlencode($nomeProgetto));
    exit;
}

// Connessione al database MySQL
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    $_SESSION['error'] = "Errore di connessione al DB.";
    header("Location: inserisci_componenti.php?progetto=" . urlencode($nomeProgetto));
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Query per inserire o aggiornare una componente in un progetto hardware
    $stmt = $conn->prepare("INSERT INTO Lista_Componenti (nome_Componente, nome_Progetto, quantita)
                            VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE quantita = quantita + VALUES(quantita)");
    $stmt->bind_param("ssi", $nomeComponente, $nomeProgetto, $quantita);
    $stmt->execute();
    $stmt->close();

    // Scrive nel log NoSQL l'evento di inserimento/aggiornamento componente
    logEvento("Componenti aggiunte per il progetto $nomeProgetto: $quantita $nomeComponente");

    $_SESSION['success'] = "Componente aggiunta con successo.";
} catch (mysqli_sql_exception $e) {
    $_SESSION['error'] = "Errore durante l'aggiunta: " . $e->getMessage();
}

// Chiude la connessione e reindirizza alla pagina di inserimento componenti
$conn->close();
header("Location: inserisci_componenti.php?progetto=" . urlencode($nomeProgetto));
exit;
?>
