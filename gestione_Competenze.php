<?php
session_start();
require 'config.php';

// Controlla se l'utente è loggato
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Controlla se l'utente è un amministratore
$email = $_SESSION['user'];
$stmt = $pdo->prepare("SELECT * FROM Amministratore WHERE email_Utente = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Accesso negato. Solo gli amministratori possono gestire le competenze.");
}

// Aggiunta di una nuova competenza
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nuova_competenza'])) {
    $nuova_competenza = trim($_POST['nuova_competenza']);
    
    if (!empty($nuova_competenza)) {
        $insertStmt = $pdo->prepare("INSERT INTO Skill (competenza, email_Amministratore) VALUES (?, ?)");
        $insertStmt->execute([$nuova_competenza, $email]);
    }
}

// Recupera tutte le competenze
$competencesStmt = $pdo->query("SELECT * FROM Skill");
$competences = $competencesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Competenze</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Gestione delle Competenze</h2>
    <p>Amministratore: <?php echo htmlspecialchars($email); ?></p>
    
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
