<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config.php'; // Connessione al database
require_once '../includes/mongo_logger.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Verifica la connessione
if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}

// Verifica se l'utente è autenticato
if (!isset($_SESSION['user_email'])) {
    header("Location: ../login/login.php");
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

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Abilita eccezioni
    $stmt = $conn->prepare("CALL InviaCandidatura(?, ?, ?)");
    $stmt->bind_param("sis", $email_utente, $id_profilo, $nome_progetto);
    $stmt->execute();
    $_SESSION['success'] = "Candidatura inviata con successo!";
    logEvento("L'utente $email_utente si è candidato per il profilo $id_profilo nel progetto $nome_progetto");
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    // Salva il messaggio di errore della procedura in sessione
    $_SESSION['error'] = "" . $e->getMessage();
}

$conn->close();

// Reindirizza alla pagina con i profili richiesti per il progetto
header("Location: visualizza_Profili.php?nome_progetto=" . urlencode($nome_progetto));
exit();
?>
