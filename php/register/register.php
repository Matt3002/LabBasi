<?php
// Inclusione del file di configurazione per la connessione al database
require 'config.php';
$conn = new mysqli($host, $username, $password, $dbname);

// Abilita la visualizzazione degli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Controllo se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupero dei dati inviati dal form
    $email = $_POST['email'];
    $nickname = $_POST['nickname'];
    // Hash della password per sicurezza
    $password = $_POST['password'];
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $anno_nascita = $_POST['anno_nascita'];
    $luogo_nascita = $_POST['luogo_nascita'];

    // Preparazione della query SQL che chiama la stored procedure esistente per la registrazione dell'utente
    $stmt = $conn->prepare("CALL RegistrazioneUtente(?, ?, ?, ?, ?, ?, ?)");
    
    // Associazione dei parametri in base alla stored procedure definita nel database
    $stmt->bind_param("sssssis", $email, $nickname, $password, $nome, $cognome, $anno_nascita, $luogo_nascita);
    
    // Esecuzione della query e gestione dell'esito
    if ($stmt->execute()) {
        echo "Registrazione completata con successo!";
    } else {
        echo "Errore nella registrazione: " . $stmt->error;
    }
    
    // Chiusura dello statement e della connessione
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione Utente</title>
</head>
<body>
    <!-- Form per la registrazione dell'utente -->
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
</body>
</html>
