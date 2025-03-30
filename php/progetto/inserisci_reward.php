<?php
session_start();
require '../config.php';
require_once '../includes/mongo_logger.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $username, $password, $dbname);

$email_creatore = $_SESSION['user_email'] ?? null;
$esito = '';

if (!$email_creatore) {
    die("<div class='msg error'>Accesso negato. Devi effettuare il login.</div>");
}

// Recupera i progetti del creatore per la tendina
$progetti = [];
try {
    $stmt = $conn->prepare("SELECT nome FROM Progetto WHERE email_Creatore = ?");
    $stmt->bind_param("s", $email_creatore);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $progetti[] = $row['nome'];
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $esito = "<div class='msg error'>Errore caricamento progetti: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Gestione del form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $descrizione = $_POST["descrizione"];
        $nome_progetto = $_POST["nome_progetto"];

        if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
            throw new Exception("Errore durante l'upload dell'immagine.");
        }

        $foto_temp = $_FILES["foto"]["tmp_name"];
        $foto_blob = file_get_contents($foto_temp);

        $stmt = $conn->prepare("CALL InserisciReward(?, ?, ?)");
        $stmt->bind_param("sss", $descrizione, $foto_blob, $nome_progetto);
        $stmt->execute();

        $esito = "<div class='msg success'>Reward inserita con successo!</div>";
        logEvento("Inserita nuova reward per il progetto $nome_progetto");
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
            $esito = "<div class='msg error'>" . htmlspecialchars($matches[1]) . "</div>";
        } else {
            $esito = "<div class='msg error'>Errore MySQL: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } catch (Exception $e) {
        $esito = "<div class='msg error'>Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Inserisci Reward</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/options.css">
    <style>
        body{
            text-align: center;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
            margin-top: 200px;
        }

        form {
            margin-top: 200px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        textarea {
            height: 80px;
        }

        input[type="submit"] {
            margin-top: 20px;
            background-color: #3aaa06;
            color: white;
            border: none;
            padding: 10px 16px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color:rgb(49, 136, 9);
        }

        .msg {
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            max-width: 600px;
            margin: 15px auto;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<?php include_once realpath(__DIR__ . '/../includes/header.php'); ?>
<?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>
<h2>Inserisci una Reward per uno dei tuoi progetti</h2>
<?= $esito ?>

<form method="post" enctype="multipart/form-data">
    <label>Descrizione Reward:</label>
    <textarea name="descrizione" required></textarea>

    <label>Immagine (file):</label>
    <input type="file" name="foto" accept="image/*" required>

    <label>Seleziona il Progetto:</label>
    <select name="nome_progetto" required>
        <option value="">-- Seleziona un progetto --</option>
        <?php foreach ($progetti as $nome): ?>
            <option value="<?= htmlspecialchars($nome) ?>"><?= htmlspecialchars($nome) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="submit" value="Inserisci Reward">
</form>
<?php include_once realpath(__DIR__ . '/../includes/footer.php'); ?>

</body>
</html>
