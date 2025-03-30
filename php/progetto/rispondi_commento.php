<?php
session_start();
require '../config.php';
require_once '../includes/mongo_logger.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (!isset($_SESSION['user_email'])) {
        throw new Exception("Errore: Accesso non autorizzato");
    }

    $emailCreatore = $_SESSION['user_email'];
    $commentId = $_POST['commentId'];
    $testo = $_POST['testo'];

    // Connessione al DB
    $conn = new mysqli($host, $username, $password, $dbname);

    // Verifica che l'utente sia un creatore
    $stmt = $conn->prepare("SELECT 1 FROM Creatore WHERE email_Utente = ?");
    $stmt->bind_param("s", $emailCreatore);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Errore: Solo i creatori possono rispondere ai commenti");
    }

    // Inserimento risposta tramite procedura
    $stmt = $conn->prepare("CALL RispondiCommento(?, ?, ?)");
    $stmt->bind_param("iss", $commentId, $testo, $emailCreatore);
    $stmt->execute();

    echo "success";
    logEvento("Il creatore $emailCreatore ha risposto al commento $commentId");
    $stmt->close();
    $conn->close();

} catch (mysqli_sql_exception $e) {
    echo "Errore MySQL: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    echo "Errore: " . htmlspecialchars($e->getMessage());
}
?>
