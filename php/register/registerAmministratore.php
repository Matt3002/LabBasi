<?php
// Inclusione del file di configurazione per la connessione al database
require '../config.php';
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
    $codice_sicurezza = $_POST['codice_sicurezza'];

    // Preparazione della query SQL che chiama la stored procedure per la registrazione dell'utente
    $stmt = $conn->prepare("CALL RegistrazioneUtente(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $email, $nickname, $password, $nome, $cognome, $anno_nascita, $luogo_nascita);
    
    // Esecuzione della query e gestione dell'esito
    if ($stmt->execute()) {
        // Dopo la registrazione come utente, inseriamo il codice di sicurezza nella tabella Amministratore
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO Amministratore (email_Utente, codice_sicurezza) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $codice_sicurezza);
        
        if ($stmt->execute()) {
            echo "<script>
            alert('Registrazione amministratore completata con successo! Ora verrai reindirizzato al login.');
            window.location.href = 'loginAmministratore.php';
          </script>";
        } else {
            echo "<script>
                alert('Errore nell' aggiunta del codice di sicurezza: " . addslashes($stmt->error) . "');
              </script>";
        }
    } else {
        echo "<script>
                alert('Errore nella registrazione: " . addslashes($stmt->error) . "');
              </script>";
    }
    
    // Chiusura dello statement e della connessione
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione Amministratore</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <!-- Form per la registrazione dell'amministratore -->
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
