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
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $anno_nascita = $_POST['anno_nascita'];
    $luogo_nascita = $_POST['luogo_nascita'];
    $affidabilita = 0; // Valore iniziale impostato a 0

    // Preparazione della query SQL che chiama la stored procedure per la registrazione dell'utente
    $stmt = $conn->prepare("CALL RegistrazioneUtente(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $email, $nickname, $password, $nome, $cognome, $anno_nascita, $luogo_nascita);
    
    // Esecuzione della query e gestione dell'esito
    if ($stmt->execute()) {
        // Dopo la registrazione come utente, inseriamo l'affidabilità nella tabella Creatore
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO Creatore (email_Utente, nr_progetti, affidabilita) VALUES (?, 0, ?)");
        $stmt->bind_param("si", $email, $affidabilita);
        
         echo "<script>
            alert('Registrazione creatore completata con successo! Ora verrai reindirizzato al login.');
            window.location.href = 'loginCreatore.php';
          </script>";
        
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
    <title>Registrazione Creatore</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <!-- Form per la registrazione del creatore -->
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="nickname" placeholder="Nickname" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="text" name="cognome" placeholder="Cognome" required>
        <input type="number" name="anno_nascita" placeholder="Anno di nascita" required>
        <input type="text" name="luogo_nascita" placeholder="Luogo di nascita" required>
        <button type="submit">Registrati come Creatore</button>
    </form>
</body>
</html>
