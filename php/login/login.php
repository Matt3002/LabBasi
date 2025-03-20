<?php
session_start();
require '../config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password1 = trim($_POST['password']);

    if (empty($email) || empty($password1)) {
        $error = "Email e password obbligatorie";
    } else {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $conn = new mysqli($host, $username, $password, $dbname);

            $stmt = $conn->prepare("CALL AutenticazioneUtente(?, ?)");
            $stmt->bind_param("ss", $email, $password1);
            $stmt->execute();
            $stmt->close();
            $conn->next_result();

            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = "Utente";
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
    <title>Login</title>
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
        <h2>Login</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Accedi</button>
            <?php if ($error): ?>
                <div class="error-msg"><?=htmlspecialchars($error)?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
