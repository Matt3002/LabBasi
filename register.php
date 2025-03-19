<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Registrazione Utente</h2>
        <form action="register.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="nickname" placeholder="Nickname" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="cognome" placeholder="Cognome" required>
            <input type="number" name="anno_nascita" placeholder="Anno di Nascita" required>
            <input type="text" name="luogo_nascita" placeholder="Luogo di Nascita" required>

            <!-- Selezione del tipo di utente -->
            <label for="tipo_utente">Tipo di utente:</label>
            <select name="tipo_utente" onchange="this.form.submit()" required>
                <option value="">Seleziona Tipo</option>
                <option value="Amministratore" <?= (isset($_POST['tipo_utente']) && $_POST['tipo_utente'] == 'Amministratore') ? 'selected' : '' ?>>Amministratore</option>
                <option value="Creatore" <?= (isset($_POST['tipo_utente']) && $_POST['tipo_utente'] == 'Creatore') ? 'selected' : '' ?>>Creatore</option>
            </select>

            <!-- Campo codice sicurezza visibile solo se l'utente ha selezionato "Amministratore" -->
            <?php if (isset($_POST['tipo_utente']) && $_POST['tipo_utente'] == 'Amministratore'): ?>
                <input type="text" name="codice_sicurezza" placeholder="Codice Sicurezza (Solo per Amministratori)" required>
            <?php endif; ?>

            <button type="submit">Registrati</button>
        </form>
        <a href="login.php" class="link-login">Hai già un account? Accedi</a>
    </div>
</body>
</html>

<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $nickname = trim($_POST['nickname']);
    $password = trim($_POST['password']);
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $anno_nascita = trim($_POST['anno_nascita']);
    $luogo_nascita = trim($_POST['luogo_nascita']);
    $tipo_utente = trim($_POST['tipo_utente']);
    $codice_sicurezza = isset($_POST['codice_sicurezza']) ? trim($_POST['codice_sicurezza']) : null;

    if (empty($email) || empty($nickname) || empty($password) || empty($nome) || empty($cognome) || empty($anno_nascita) || empty($luogo_nascita) || empty($tipo_utente)) {
        die("Tutti i campi sono obbligatori!");
    }

    if ($tipo_utente === "Amministratore" && empty($codice_sicurezza)) {
        die("Il codice di sicurezza è obbligatorio per gli amministratori.");
    }

    // Connessione al database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    // Inserimento nella tabella Utente
    $stmt = $conn->prepare("INSERT INTO Utente (email, nickname, password, nome, cognome, anno_nascita, luogo_nascita) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $email, $nickname, $password, $nome, $cognome, $anno_nascita, $luogo_nascita);
    
    if ($stmt->execute()) {
        // Se l'utente è un Amministratore, lo aggiungiamo alla tabella Amministratore
        if ($tipo_utente === "Amministratore") {
            $stmt = $conn->prepare("INSERT INTO Amministratore (email_Utente, codice_sicurezza) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $codice_sicurezza);
            $stmt->execute();
        }

        // Se l'utente è un Creatore, lo aggiungiamo alla tabella Creatore
        if ($tipo_utente === "Creatore") {
            $nr_progetti = 0;
            $affidabilita = 0;
            $stmt = $conn->prepare("INSERT INTO Creatore (email_Utente, nr_progetti, affidabilità) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $email, $nr_progetti, $affidabilita);
            $stmt->execute();
        }

        echo "Registrazione completata con successo!";
        header("Location: login.php");
        exit();
    } else {
        echo "Errore nella registrazione: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
