<?php
session_start();
require 'config.php'; // Connessione al database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        die("Email e password sono obbligatori!");
    }

    // Connessione al database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    // Uso della stored procedure AutenticazioneUtente
    $stmt = $conn->prepare("CALL AutenticazioneUtente(?, ?)");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $stmt->fetch_assoc();
        $_SESSION['user_email'] = $email;

        switch ($user['ruolo']) {
            case 'Amministratore':
                $_SESSION['user_role'] = "Amministratore";
                header("Location: dashboard_admin.php");
                exit();

            case 'Creatore':
                $_SESSION['user_role'] = "Creatore";
                header("Location: dashboard_creatore.php");
                exit();

            default:
                $_SESSION['user_role'] = "Utente";
                header("Location: dashboard.php");
                exit();
        }
    }

    echo "Email o password errati!";

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Accedi</button>
        </form>
        <a href="register.php" class="link-register">Non hai un account? Registrati</a>
    </div>
</body>
</html>
