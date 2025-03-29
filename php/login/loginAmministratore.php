<?php
session_start();
require '../config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password1 = trim($_POST['password']);
    $securityCode = trim($_POST['securityCode']);

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
    <link rel="stylesheet" href="../../css/styleLogin.css">
    <title>Login Amministratore</title>
    <style>
        .error-msg {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
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
            <?php if ($error): ?>
                <div class="error-msg"><?=htmlspecialchars($error)?></div>
            <?php endif; ?>
        </form>
        <p style="text-align:center; margin-top:10px;">
        Non sei ancora registrato? <a href="../register/registerAmministratore.php">Vai alla pagina di registrazione</a>
        </p>
    </div>
</body>
</html>