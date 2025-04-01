<?php
session_start();
require '../config.php';

//Recupera email del Creatore e il nome del Progetto
$emailCreatore = $_SESSION['user_email'] ?? null;
$nomeProgetto = $_GET['progetto'] ?? null;

if (!$emailCreatore || !$nomeProgetto) {
    die("Accesso non autorizzato.");
}

// Connessione al DB
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Recupero candidature in attesa
$stmt = $conn->prepare("SELECT C.email_Utente, C.id_Profilo, P.nome AS nome_profilo
                        FROM Candidatura C
                        JOIN Profilo P ON C.id_Profilo = P.id
                        WHERE C.nome_Progetto = ? AND C.stato = 'in attesa'");
$stmt->bind_param("s", $nomeProgetto);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestisci Candidature</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <style>
        .candidatura-box {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            background: #f5f5f5;
        }
        .candidatura-actions button {
            margin-right: 10px;
            padding: 6px 10px;
        }
    </style>
</head>
<body>
    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>
    <?php include_once realpath(__DIR__ . '/../includes/header.php'); ?>
    <div class="content">
        <h2>Candidature in attesa per il progetto: <?= htmlspecialchars($nomeProgetto) ?></h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="candidatura-box">
                    <p><strong>Email:</strong> <?= htmlspecialchars($row['email_Utente']) ?></p>
                    <p><strong>Profilo:</strong> <?= htmlspecialchars($row['nome_profilo']) ?></p>

                    <form method="post" action="gestisci_candidature.php?progetto=<?= urlencode($nomeProgetto) ?>">
                        <input type="hidden" name="email_Utente" value="<?= htmlspecialchars($row['email_Utente']) ?>">
                        <input type="hidden" name="id_Profilo" value="<?= htmlspecialchars($row['id_Profilo']) ?>">
                        <button type="submit" name="azione" value="accettata">Accetta</button>
                        <button type="submit" name="azione" value="rifiutata">Rifiuta</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nessuna candidatura in attesa.</p>
        <?php endif; ?>
    </div>
    <?php include_once realpath(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>

<?php
// Gestione post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati inviati dal form
    $emailUtente = $_POST['email_Utente'] ?? '';
    $idProfilo = $_POST['id_Profilo'] ?? '';
    $stato = $_POST['azione'] ?? '';

    // Verifica che lo stato sia valido (accettata o rifiutata)
    if (in_array($stato, ['accettata', 'rifiutata'])) {
        // Prepara la chiamata alla stored procedure per aggiornare la candidatura
        $stmt = $conn->prepare("CALL GestisciCandidatura(?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $emailUtente, $idProfilo, $nomeProgetto, $emailCreatore, $stato);
        
        try {
            $stmt->execute();
            echo "<script>alert('Candidatura aggiornata con successo.'); window.location.href='gestisci_candidature.php?progetto=" . urlencode($nomeProgetto) . "';</script>";
        } catch (mysqli_sql_exception $e) {
            echo "<script>alert('Errore: " . $e->getMessage() . "');</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>
