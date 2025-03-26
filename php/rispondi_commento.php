<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_email'])) {
    die("Errore: Accesso non autorizzato");
}

$emailCreatore = $_SESSION['user_email'];
$commentId = $_POST['commentId'];
$testo = $_POST['testo'];

// Verifica che l'utente sia un creatore
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT 1 FROM Creatore WHERE email_Utente = ?");
$stmt->bind_param("s", $emailCreatore);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Errore: Solo i creatori possono rispondere ai commenti");
}

// Chiamata alla stored procedure
$stmt = $conn->prepare("CALL RispondiCommento(?, ?, ?)");
$stmt->bind_param("iss", $commentId, $testo, $emailCreatore);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Errore durante l'inserimento della risposta: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>