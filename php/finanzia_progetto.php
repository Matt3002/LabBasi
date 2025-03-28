<?php
session_start();
require 'config.php';

$conn = new mysqli($host, $username, $password, $dbname);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Attiva eccezioni per mysqli

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_email'])) {
    die(json_encode(["status" => "error", "message" => "Utente non autenticato."]));
}

$nome_progetto = $_GET['nome_progetto'] ?? '';

if (empty($nome_progetto)) {
    die("<script>alert('Errore: Nome progetto mancante.'); window.location.href = 'visualizza_Progetti.php';</script>");
}

$email_utente = $_SESSION['user_email'];

echo "<style>
    body {
        font-family: Arial, sans-serif;
        padding: 30px;
        background-color: #f5f5f5;
    }
    h2, h3 {
        color: #333;
    }
    form {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        max-width: 500px;
    }
    input[type='text'], input[type='number'], input[type='email'], select, textarea {
        width: 100%;
        padding: 10px;
        margin: 8px 0 16px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    input[type='submit'] {
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    input[type='submit']:hover {
        background-color: #218838;
    }
    .msg {
        padding: 10px;
        margin: 15px 0;
        border-radius: 5px;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>";

$ha_gia_finanziato = false;
try {
    $oggi = date("Y-m-d");
    $check = $conn->prepare("SELECT 1 FROM Finanziamento WHERE email_Utente = ? AND nome_Progetto = ? AND data = ?");
    $check->bind_param("sss", $email_utente, $nome_progetto, $oggi);
    $check->execute();
    $result_check = $check->get_result();
    if ($result_check->num_rows > 0) {
        $ha_gia_finanziato = true;
    }
    $check->close();
} catch (mysqli_sql_exception $e) {
    echo "<div class='msg error'>Errore di controllo finanziamento: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Selezione reward
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['scegli_reward'])) {
    try {
        $codice_reward = $_POST['codice_reward'];

        $stmt = $conn->prepare("CALL SelezionaReward(?, ?, ?)");
        $stmt->bind_param("ssi", $email_utente, $nome_progetto, $codice_reward);
        $stmt->execute();
        echo "<div class='msg success'>Reward selezionata con successo!</div>";
        $stmt->close();
        header("Location: visualizza_Progetti.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
            echo "<div class='msg error'>" . htmlspecialchars($matches[1]) . "</div>";
        } else {
            echo "<div class='msg error'>Errore nella selezione della reward.</div>";
        }
    }
}

// Finanziamento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finanzia'])) {
    try {
        $importo = $_POST['importo'];
        $data = date("Y-m-d");

        $stmt = $conn->prepare("CALL FinanziaProgetto(?, ?, ?, ?, NULL)");
        $stmt->bind_param("ssds", $email_utente, $nome_progetto, $importo, $data);
        $stmt->execute();
        echo "<div class='msg success'>Finanziamento registrato con successo!</div>";
        $ha_gia_finanziato = true;
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
            echo "<div class='msg error'>" . htmlspecialchars($matches[1]) . "</div>";
        } else {
            echo "<div class='msg error'>Errore nel finanziamento.</div>";
        }
    }
}

// Output dinamico in base allo stato
// Verifica se ha già finanziato
if ($ha_gia_finanziato) {
    // Controlla se esiste già una reward associata
    $checkReward = $conn->prepare("SELECT codice_Reward FROM Finanziamento WHERE email_Utente = ? AND nome_Progetto = ?");
    $checkReward->bind_param("ss", $email_utente, $nome_progetto);
    $checkReward->execute();
    $resReward = $checkReward->get_result();
    $rewardAssociata = $resReward->fetch_assoc()['codice_Reward'] ?? null;
    $checkReward->close();

    //  Se NON ha ancora scelto una reward, mostra il form
    if ($rewardAssociata === null) {
        $query = $conn->prepare("SELECT codice, descrizione FROM Reward WHERE nome_Progetto = ?");
        $query->bind_param("s", $nome_progetto);
        $query->execute();
        $result = $query->get_result();

        echo "<h3>Seleziona una reward disponibile per questo progetto:</h3>";
        echo "<form method='post'>";
        echo "<label>Reward:</label>";
        echo "<select name='codice_reward' required>";
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['codice'] . "'>" . htmlspecialchars($row['descrizione']) . "</option>";
        }
        echo "</select>";
        echo "<input type='submit' name='scegli_reward' value='Conferma Reward'>";
        echo "</form>";

        $query->close();
    } else {
        echo "<div class='msg success'>Hai già selezionato una reward per questo progetto.</div>";
    }
} else {
    // FORM per finanziare un progetto
    echo "<h2>Finanzia il progetto: " . htmlspecialchars($nome_progetto) . "</h2>";
    echo "<form method='post'>";
    echo "<label>Importo (€):</label>";
    echo "<input type='number' step='0.01' name='importo' required>";
    echo "<input type='submit' name='finanzia' value='Finanzia Ora'>";
    echo "</form>";
}
?>
