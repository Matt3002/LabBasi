<?php
require '../config.php';
require_once '../includes/mongo_logger.php';

// Visualizzazione di tutti gli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Se il modulo è stato inviato tramite metodo POST recupera i dati inseriti nel form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $nickname = trim($_POST['nickname']);
    $password1 = trim($_POST['password']);
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $anno_nascita = $_POST['anno_nascita'];
    $luogo_nascita = trim($_POST['luogo_nascita']);
    $affidabilita = 0;

    try {
        // Attiva eccezioni su errori MySQLi
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($host, $username, $password, $dbname);

        // Registrazione utente via procedura
        $stmt = $conn->prepare("CALL RegistrazioneUtente(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $email, $nickname, $password1, $nome, $cognome, $anno_nascita, $luogo_nascita);
        $stmt->execute();
        $stmt->close();
        $conn->next_result();

        // Inserimento nella tabella Creatore
        $stmt = $conn->prepare("INSERT INTO Creatore (email_Utente, nr_progetti, affidabilita) VALUES (?, 0, ?)");
        $stmt->bind_param("si", $email, $affidabilita);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        // Registra l'evento su MongoDB
        logEvento("Nuovo creatore registrato: $email");

        // In caso di successo mostra un messaggio e reindirizza al login altrimenti cattura l'errore e mostra un avviso
        echo "<script>
            alert('Registrazione creatore completata con successo! Ora verrai reindirizzato al login.');
            window.location.href = '../login/loginCreatore.php';
        </script>";
        exit();

    } catch (mysqli_sql_exception $e) {
        echo "<script>
            alert(' " . addslashes($e->getMessage()) . "');
            window.location.href = '../login/login.php';
        </script>";
        if (isset($stmt)) $stmt->close();
        if (isset($conn)) $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione Creatore</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <a class="btn-home" href="../../index.html">← Torna alla Home</a>
        <h2>Registrazione Creatore</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="nickname" placeholder="Nickname" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="cognome" placeholder="Cognome" required>
            <input type="number" name="anno_nascita" placeholder="Anno di nascita" required>
            <input type="text" name="luogo_nascita" placeholder="Luogo di nascita" required>
            <button type="submit">Registrati</button>
            
        </form>
        <p>
            Sei registrato? <a href="../login/loginCreatore.php">Vai al login</a>
        </p>
    </div>
</body>
</html>
