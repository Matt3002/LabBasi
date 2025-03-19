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

    // Controllo se l'utente esiste
    $stmt = $conn->prepare("SELECT email, password FROM Utente WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_email, $db_password);
        $stmt->fetch();

        // Confronto diretto della password (senza hashing)
        if ($password === $db_password) {
            $_SESSION['user_email'] = $db_email;

            // Controllo se l'utente è un Amministratore
            $stmt = $conn->prepare("SELECT codice_sicurezza FROM Amministratore WHERE email_Utente = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $_SESSION['user_role'] = "Amministratore";
                header("Location: dashboard_admin.php");
                exit();
            }

            // Controllo se l'utente è un Creatore
            $stmt = $conn->prepare("SELECT nr_progetti FROM Creatore WHERE email_Utente = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $_SESSION['user_role'] = "Creatore";
                header("Location: dashboard_creatore.php");
                exit();
            }

            // Se non è né Amministratore né Creatore, allora è solo un utente standard
            $_SESSION['user_role'] = "Utente";
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Email o password errati!";
        }
    } else {
        echo "Email o password errati!";
    }

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
