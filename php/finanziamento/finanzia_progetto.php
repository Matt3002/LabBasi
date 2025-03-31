<?php
session_start();
require '../config.php';
require_once '../includes/mongo_logger.php';

$basePath = "http://localhost/bostarter/LabBasi/php/";

$conn = new mysqli($host, $username, $password, $dbname);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_email'])) {
    die(json_encode(["status" => "error", "message" => "Utente non autenticato."]));
}

$email_utente = $_SESSION['user_email'];
$nome_progetto = $_GET['nome_progetto'] ?? '';
$data_oggi = date("Y-m-d");

if (empty($nome_progetto)) {
    die("<script>alert('Errore: Nome progetto mancante.'); window.location.href = '../progetto/visualizza_Progetti.php';</script>");
}

$mostraReward = false;
$esito = "";
$rewardAssociata = null;

// Step 1: Se arriva un POST con finanziamento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finanzia'])) {
    try {
        $importo = floatval($_POST['importo']);
        $stmt = $conn->prepare("CALL FinanziaProgetto(?, ?, ?, ?, NULL)");
        $stmt->bind_param("ssds", $email_utente, $nome_progetto, $importo, $data_oggi);
        $stmt->execute();
        $stmt->close();

        $mostraReward = true; // Abilita il form reward
        $esito = "<div class='msg success'>Finanziamento registrato con successo! Ora seleziona una reward.</div>";
        logEvento("L'utente $email_utente ha erogato un finanziamento per il progetto $nome_progetto");
    } catch (mysqli_sql_exception $e) {
        if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
            $esito = "<div class='msg error'>" . htmlspecialchars($matches[1]) . "</div>";
        } else {
            $esito = "<div class='msg error'>Errore durante il finanziamento.</div>";
        }
    }
}

// Step 2: Se arriva il form di selezione reward
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['scegli_reward'])) {
    try {
        $codice_reward = $_POST['codice_reward'];
        $stmt = $conn->prepare("CALL SelezionaReward(?, ?, ?)");
        $stmt->bind_param("ssi", $email_utente, $nome_progetto, $codice_reward);
        $stmt->execute();
        $stmt->close();

        header("Location: ../progetto/visualizza_Progetti.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
            $esito = "<div class='msg error'>" . htmlspecialchars($matches[1]) . "</div>";
        } else {
            $esito = "<div class='msg error'>Errore tecnico: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        $mostraReward = true; // Resta sulla reward
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Finanzia Progetto</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 30px; 
            justify-self: center;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin-bottom: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 16px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type='submit'] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        input[type='submit']:hover {
            background-color: #218838;
        }
        .msg {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #218838;
        }
    </style>
</head>
<body>

<h2>Finanzia il progetto: <?php echo htmlspecialchars($nome_progetto); ?></h2>

<?php echo $esito; ?>

<?php if (!$mostraReward): ?>
<form method="post">
    <label>Importo (€):</label>
    <input type="number" step="0.01" min="0.01" name="importo" required>
    <input type="submit" name="finanzia" value="Finanzia Ora">
</form>
<a class="back-link" href="../progetto/miei_progetti.php">← Torna ai tuoi progetti</a>
<?php endif; ?>

<?php if ($mostraReward): ?>
<form method="post">
    <label>Seleziona una reward disponibile:</label>
    <select name="codice_reward" required>
        <?php
        $query = $conn->prepare("SELECT codice, descrizione FROM Reward WHERE nome_Progetto = ?");
        $query->bind_param("s", $nome_progetto);
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['codice'] . "'>" . htmlspecialchars($row['descrizione']) . "</option>";
        }
        $query->close();
        ?>
    </select>
    <input type="submit" name="scegli_reward" value="Conferma Reward">
</form>
<?php endif; ?>


</body>
</html>
