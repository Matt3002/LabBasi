<?php
session_start();
require 'config.php'; // Connessione al database

$conn = new mysqli($host, $username, $password, $dbname);

// Abilita la visualizzazione degli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se l'utente è autenticato
if (!isset($_SESSION['user_email'])) {
    die(json_encode(["status" => "error", "message" => "Utente non autenticato."]));
}

// Recupera il nome del progetto tramite GET
$nome_progetto = $_GET['nome_progetto'] ?? '';

if (empty($nome_progetto)) {
    die("<script>alert('Errore: Nome progetto mancante.'); window.location.href = 'visualizza_Progetti.php';</script>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupero dei dati inviati dal form
    $email_utente = $_SESSION['user_email']; // L'utente loggato che finanzia
    $nome_progetto = $_POST['nome_progetto'];
    $importo = $_POST['importo'];
    $data = date('Y-m-d'); // Data odierna
    $codice_reward = $_POST['codice_reward'] ?? null; // Reward opzionale

    // Controllo dei parametri
    if (empty($nome_progetto) || empty($importo) || $importo <= 0) {
        echo "<script>alert('Errore: Importo non valido o dati mancanti.'); window.history.back();</script>";
        exit();
    }

    // Chiamata alla procedura memorizzata
    $stmt = $conn->prepare("CALL FinanziaProgetto(?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsd", $email_utente, $nome_progetto, $importo, $data, $codice_reward);

    if ($stmt->execute()) {
        echo "<script>
                alert('Finanziamento completato con successo!');
                window.location.href = 'visualizza_Progetti.php';
              </script>";
        exit();
    } else {
        echo "<script>alert('Errore durante il finanziamento: " . addslashes($stmt->error) . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Finanzia un Progetto</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<form method="POST">
    <input type="hidden" name="nome_progetto" value="<?php echo htmlspecialchars($nome_progetto ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <p><strong>Progetto:</strong> <?php echo htmlspecialchars($nome_progetto ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
    <input type="number" step="0.01" name="importo" placeholder="Importo da finanziare (€)" required>
    <input type="text" name="codice_reward" placeholder="Codice Reward (opzionale)">
    <button type="submit">Finanzia Progetto</button>
</form>

</body>
</html>
