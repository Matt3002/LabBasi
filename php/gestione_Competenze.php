<?php
session_start();
require 'config.php'; // Contiene $host, $username, $password, $dbname

// Controlla se l'utente è loggato e ha ruolo "Amministratore"
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'Amministratore') {
    header("Location: login/loginAmministratore.php");
    exit;
}

$email = $_SESSION['user_email'];
$esito = null;
$error = null;

// Connessione al database
$mysqli = new mysqli($host, $username, $password, $dbname);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Per abilitare eccezioni mysqli

// Aggiunta di una nuova competenza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuova_competenza'])) {
    $nuova_competenza = trim($_POST['nuova_competenza']);

    if (!empty($nuova_competenza)) {
        try {
            $stmt = $mysqli->prepare("CALL AggiungiCompetenza(?, ?)");
            $stmt->bind_param("ss", $nuova_competenza, $email);
            $stmt->execute();
            $stmt->close();
            $esito = "Competenza aggiunta con successo!";
        } catch (mysqli_sql_exception $e) {
            // Estrai solo il messaggio della SIGNAL
            if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
                $error = $matches[1];
            } else {
                $error = "Errore imprevisto durante l'inserimento.";
            }
        }
    }
}

// Recupera tutte le competenze esistenti
$competences = [];
$result = $mysqli->query("SELECT * FROM Skill ORDER BY competenza ASC");
while ($row = $result->fetch_assoc()) {
    $competences[] = $row;
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Competenze</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Gestione delle Competenze</h2>
    <p>Amministratore: <?php echo htmlspecialchars($email); ?></p>

    <?php if ($esito): ?>
        <p class="success"><?php echo htmlspecialchars($esito); ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="gestione_competenze.php">
        <label>Nuova Competenza:</label>
        <input type="text" name="nuova_competenza" required>
        <button type="submit">Aggiungi</button>
    </form>

    <h3>Competenze Esistenti</h3>
    <ul>
        <?php foreach ($competences as $competenza): ?>
            <li><?php echo htmlspecialchars($competenza['competenza']); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
