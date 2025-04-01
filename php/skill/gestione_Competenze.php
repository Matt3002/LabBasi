<?php
session_start();
require '../config.php'; // Contiene $host, $username, $password, $dbname
require_once '../includes/mongo_logger.php';

// Controlla se l'utente è loggato e ha ruolo "Amministratore"
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'Amministratore') {
    header("Location: ../login/loginAmministratore.php");
    exit;
}

$email = $_SESSION['user_email'];
$esito = null;
$error = null;

// Connessione al database
$mysqli = new mysqli($host, $username, $password, $dbname);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
            logEvento("L'admin $email ha creato la competenza $nuova_competenza");
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
    <link rel="stylesheet" href="../../css/dashboard.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            margin: 0;
            padding: 0 20px 50px auto;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: white;
            border-radius: 10px;
            padding: 30px 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2, h3 {
            color: green;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #006600;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        ul li {
            padding: 8px;
            background-color: #e9f5e9;
            margin: 5px 0;
            border-left: 4px solid green;
            border-radius: 4px;
        }

        .success {
            color: green;
            font-weight: bold;
            margin-top: 10px;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>

    <div class="container">
        <h2>Gestione delle Competenze</h2>
        <p>Amministratore: <?php echo htmlspecialchars($email); ?></p>

        <?php if ($esito): ?>
            <p class="success"><?php echo htmlspecialchars($esito); ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="gestione_competenze.php">
            <label for="nuova_competenza">Nuova Competenza:</label>
            <input type="text" name="nuova_competenza" id="nuova_competenza" required>
            <button type="submit">Aggiungi</button>
        </form>

        <h3>Competenze Esistenti</h3>
        <ul>
            <?php foreach ($competences as $competenza): ?>
                <li><?php echo htmlspecialchars($competenza['competenza']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>

