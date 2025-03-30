<?php
require '../config.php';
require_once '../includes/mongo_logger.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $nickname = $_POST['nickname'];
    $password1 = $_POST['password'];  // lasciata semplice come da richiesta
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $anno_nascita = $_POST['anno_nascita'];
    $luogo_nascita = $_POST['luogo_nascita'];
    $affidabilita = 0;

    try {
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

        // Log MongoDB
        logEvento("Nuovo creatore registrato: $email");

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
    <!-- Form per la registrazione del creatore -->
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
        <p style="text-align:center; margin-top:10px;">
            Sei registrato? <a href="../login/loginCreatore.php">Vai al login</a>
        </p>
    </div>
</body>
</html>
