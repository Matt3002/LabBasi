<?php
require '../config.php';
require_once '../includes/mongo_logger.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $nickname = $_POST['nickname'];
    $user_password = $_POST['password'];  // rinominata per evitare conflitto con config
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $anno_nascita = $_POST['anno_nascita'];
    $luogo_nascita = $_POST['luogo_nascita'];
    $codice_sicurezza = $_POST['codice_sicurezza'];

    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($host, $username, $password, $dbname);

        // Inserimento nella tabella Utente tramite procedura
        $stmt = $conn->prepare("CALL RegistrazioneUtente(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $email, $nickname, $user_password, $nome, $cognome, $anno_nascita, $luogo_nascita);
        $stmt->execute();
        $stmt->close();
        $conn->next_result();

        // Inserimento del codice di sicurezza nella tabella Amministratore
        $stmt = $conn->prepare("INSERT INTO Amministratore (email_Utente, codice_sicurezza) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $codice_sicurezza);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        logEvento("Nuovo amministratore registrato: $email");

        echo "<script>
            alert('Registrazione amministratore completata con successo! Ora verrai reindirizzato al login.');
            window.location.href = '../login/loginAmministratore.php';
        </script>";
        exit();

    } catch (mysqli_sql_exception $e) {
        $error = $e->getMessage();
        echo "<script>alert('" . addslashes($error) . "');</script>";
        if (isset($stmt)) $stmt->close();
        if (isset($conn)) $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione Amministratore</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="nickname" placeholder="Nickname" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="text" name="cognome" placeholder="Cognome" required>
        <input type="number" name="anno_nascita" placeholder="Anno di nascita" required>
        <input type="text" name="luogo_nascita" placeholder="Luogo di nascita" required>
        <input type="text" name="codice_sicurezza" placeholder="Codice di sicurezza" required>
        <button type="submit">Registrati come Amministratore</button>
    </form>
</body>
</html>
