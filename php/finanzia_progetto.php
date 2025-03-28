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

$email_utente = $_SESSION['user_email'];

// CSS basic
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

// Verifica se l'utente ha già finanziato il progetto
$ha_gia_finanziato = false;
$check = $conn->prepare("SELECT * FROM Finanziamento WHERE email_Utente = ? AND nome_Progetto = ?");
$check->bind_param("ss", $email_utente, $nome_progetto);
$check->execute();
$result_check = $check->get_result();
if ($result_check->num_rows > 0) {
    $ha_gia_finanziato = true;
}
$check->close();

// Selezione reward dopo finanziamento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['scegli_reward'])) {
    $codice_reward = $_POST['codice_reward'];

    $stmt = $conn->prepare("CALL SelezionaReward(?, ?, ?)");
    $stmt->bind_param("ssi", $email_utente, $nome_progetto, $codice_reward);

    if ($stmt->execute()) {
        echo "<div class='msg success'>Reward selezionata con successo!</div>";
    } else {
        echo "<div class='msg error'>Errore nella selezione della reward: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Finanziamento del progetto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finanzia'])) {
    $importo = $_POST['importo'];
    $data = date("Y-m-d");

    $stmt = $conn->prepare("INSERT INTO Finanziamento (email_Utente, nome_Progetto, importo, data) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $email_utente, $nome_progetto, $importo, $data);

    if ($stmt->execute()) {
        echo "<div class='msg success'>Finanziamento registrato con successo!</div>";
        $ha_gia_finanziato = true;
    } else {
        echo "<div class='msg error'>Errore nel finanziamento: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Mostra form di selezione reward se ha già finanziato
if ($ha_gia_finanziato) {
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
    // FORM per finanziare un progetto
    echo "<h2>Finanzia il progetto: " . htmlspecialchars($nome_progetto) . "</h2>";
    echo "<form method='post'>";
    echo "<label>Importo (€):</label>";
    echo "<input type='number' step='0.01' name='importo' required>";
    echo "<input type='submit' name='finanzia' value='Finanzia Ora'>";
    echo "</form>";
}
?>
