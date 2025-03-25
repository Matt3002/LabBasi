<?php
session_start();
include 'config.php'; // Connessione al database

header('Content-Type: application/json');

// Verifica se l'utente è autenticato
if (!isset($_SESSION['user_email'])) {
    die(json_encode(["status" => "error", "message" => "Utente non autenticato."]));
}

// Recupera i dati inviati dal frontend
$email_utente = $_SESSION['user_email']; // L'utente che sta inviando la candidatura
$id_profilo = $_POST['id_profilo']; // ID del profilo richiesto
$nome_progetto = $_POST['nome_progetto']; // Nome del progetto

try {
    // Preparazione della chiamata alla stored procedure
    $stmt = $conn->prepare("CALL InviaCandidatura(?, ?, ?)");
    $stmt->bind_param("sis", $email_utente, $id_profilo, $nome_progetto);

    if ($stmt->execute()) {
        header("Location: visualizza_profili.php?nome_progetto=" . urlencode($nome_progetto));
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Errore nell'invio della candidatura."]);
    }
    // Chiude lo statement e la connessione
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Errore: " . $e->getMessage()]);
}
?>
