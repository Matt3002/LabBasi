<?php
session_start();
require '../config.php';

$error = "";

// Se il modulo è stato inviato tramite metodo POST recupera i dati inseriti nel form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password1 = trim($_POST['password']);
    $securityCode = trim($_POST['securityCode']);  // Solo per amministratori viene richiesto il codice di sicurezza

    if (empty($email) || empty($password1) || empty($securityCode)) {
        $error = "Tutti i campi sono obbligatori";
    } else {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $conn = new mysqli($host, $username, $password, $dbname);

            $stmt = $conn->prepare("CALL AutenticazioneAmministratore(?, ?, ?)");
            $stmt->bind_param("sss", $email, $password1, $securityCode);
            $stmt->execute();
            $stmt->close();
            $conn->next_result();

            // Se l'autenticazione è riuscita avvia la sessione utente amministratore            
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = "Amministratore";
            header("Location: ../dashboard/dashboard.php");
            exit();

        } catch (mysqli_sql_exception $e) {
            $error = "" . $e->getMessage();
        }

        if (isset($conn)) $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../css/style.css">
    <title>Login Amministratore</title>
</head>
<body>
    <div class="container">
        <a class="btn-home" href="../../index.html">← Torna alla Home</a>
        <h2>Login Amministratore</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="securityCode" placeholder="Codice di sicurezza" required>
            <button type="submit">Accedi</button>

            <!-- Se è stato catturato un errore mostra il messaggio inerente -->
            <?php if ($error): ?>
                <div class="error-msg"><?=htmlspecialchars($error)?></div>
            <?php endif; ?>
        </form>
        <p>
        Non sei ancora registrato? <a href="../register/registerAmministratore.php">Vai alla pagina di registrazione</a>
        </p>
    </div>
</body>
</html>