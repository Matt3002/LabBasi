<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php'; // Connessione al database

$conn = new mysqli($host, $username, $password, $dbname);

// Verifica la connessione
if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}

// Verifica se l'utente è autenticato
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// Recupera i dati inviati dal form
$email_utente = $_SESSION['user_email'];
$id_profilo = $_POST['id_profilo'] ?? null;
$nome_progetto = $_POST['nome_progetto'] ?? null;

// Controllo dei parametri
if (empty($id_profilo) || empty($nome_progetto)) {
    $_SESSION['error'] = "Errore: dati mancanti per la candidatura.";
    header("Location: visualizza_Profili.php?nome_progetto=" . urlencode($nome_progetto));
    exit();
}

// Chiamata alla procedura memorizzata
$stmt = $conn->prepare("CALL InviaCandidatura(?, ?, ?)");
$stmt->bind_param("sis", $email_utente, $id_profilo, $nome_progetto);

if ($stmt->execute()) {
    $_SESSION['success'] = "Candidatura inviata con successo!";
} else {
    $_SESSION['error'] = "Errore durante l'invio della candidatura.";
}

$stmt->close();
$conn->close();

// Reindirizza l'utente a `visualizza_Profili.php` dopo l'invio
header("Location: visualizza_Profili.php?nome_progetto=" . urlencode($nome_progetto));
exit();
?>
