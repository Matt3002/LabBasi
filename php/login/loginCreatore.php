<?php
session_start();
require '../config.php';

$error = "";

// Se il modulo è stato inviato tramite metodo POST recupera i dati inseriti nel form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password1 = trim($_POST['password']);

    if (empty($email) || empty($password1)) {
        $error = "Email e password obbligatorie";
    } else {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $conn = new mysqli($host, $username, $password, $dbname);

            $stmt = $conn->prepare("CALL AutenticazioneCreatore(?, ?)");
            $stmt->bind_param("ss", $email, $password1);
            $stmt->execute();
            $stmt->close();
            $conn->next_result();

            // Se l'autenticazione è riuscita avvia la sessione utente creatore
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = "Creatore";
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
    <title>Login Creatore</title>
</head>
<body>
    <div class="container">
        <a class="btn-home" href="../../index.html">← Torna alla Home</a>
        <h2>Login Creatore</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Accedi</button>

            <!-- Se è stato catturato un errore mostra il messaggio inerente -->
            <?php if ($error): ?>
                <div class="error-msg"><?=htmlspecialchars($error)?></div>
            <?php endif; ?>
        </form>
        <p>
        Non sei ancora registrato? <a href="../register/registerCreatore.php">Vai alla pagina di registrazione</a>
        </p>
    </div>
</body>
</html>