<?php
session_start();
require '../config.php';

$emailSession = $_SESSION['user_email'] ?? null;

// Controlla se l'utente è loggato, altrimenti blocca l'accesso
if (!$emailSession) {
    die("<p class='error'>Errore: Devi essere loggato per vedere i tuoi progetti.</p>");
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $dbname);

    // Chiama la stored procedure per recuperare i progetti creati dall’utente loggato
    $stmt = $conn->prepare("CALL VisualizzaProgettiCreati(?)");
    $stmt->bind_param("s", $emailSession);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();

} catch (mysqli_sql_exception $e) {
    echo "<p class='error'>Errore durante il recupero dei progetti: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Progetti - Bostarter</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/options.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .success { color: green; }
        .error { color: red; }
        .project-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .comment-section {
            margin-top: 10px;
            padding-left: 10px;
            border-left: 3px solid #3aaa06; 
        }
        .project-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .project-buttons a {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            background-color: #3aaa06;
            color: white;
            font-weight: bold;
            transition: 0.3s;
            font-size: 14px;
        }
        .project-buttons a:hover {
            background-color: #2f8e04;
        }
    </style>
</head>
<body>

    <?php include_once realpath(__DIR__ . '/../includes/header.php'); ?>
    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>

    <div class="content">
        <section>
            <h2>I Miei Progetti</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($progetto = $result->fetch_assoc()) {
                    $nomeProgetto = urlencode($progetto['nome']);
                    echo "<div class='project-card'>";
                    echo "<img src='images/default_project.jpg' alt='Immagine progetto'>";
                    echo "<div class='project-info'>";
                    echo "<h3>" . htmlspecialchars($progetto['nome']) . "</h3>";
                    echo "<p>" . htmlspecialchars($progetto['descrizione']) . "</p>";
                    echo "<p><strong>Budget:</strong> " . $progetto['budget'] . " €</p>";
                    echo "<p><strong>Data Limite:</strong> " . $progetto['data_Limite'] . "</p>";
                    echo "<p><strong>Stato:</strong> " . ucfirst($progetto['stato']) . "</p>";

                    // Chiama la stored procedure per recuperare i commenti associati al progetto
                    $conn1 = new mysqli($host, $username, $password, $dbname);
                    if ($conn1->connect_error) {
                        die("Connessione fallita: " . $conn1->connect_error);
                    }
                    $stmtCommenti = $conn1->prepare("CALL VisualizzaCommentiProgetto(?)");
                    $stmtCommenti->bind_param("s", $progetto['nome']);
                    $stmtCommenti->execute();
                    $resultCommenti = $stmtCommenti->get_result();
                    $stmtCommenti->close();
                    $conn1->close();  

                    echo "<div class='comment-section'>";
                    echo "<h4>Commenti:</h4>";

                    if ($resultCommenti->num_rows > 0) {
                        while ($commento = $resultCommenti->fetch_assoc()) {
                            echo "<div class='comment'>";
                            echo "<p><strong>" . htmlspecialchars($commento['email_Utente']) . ":</strong> " . htmlspecialchars($commento['testo']) . " <br><em>Data: " . $commento['data'] . "</em></p>";

                            // Recupera la risposta associata al commento (se esiste)
                            $conn2 = new mysqli($host, $username, $password, $dbname);
                            if ($conn2->connect_error) {
                                die("Connessione fallita: " . $conn2->connect_error);
                            }
                            
                            $stmtRisposta = $conn2->prepare("SELECT testo FROM Risposta WHERE id_Commento = ?");
                            $stmtRisposta->bind_param("i", $commento['id']);
                            $stmtRisposta->execute();
                            $resultRisposta = $stmtRisposta->get_result();
                            $stmtRisposta->close();
                            $conn2->close();

                            // Mostra la risposta se presente, altrimenti campo input per rispondere
                            if ($rowRisposta = $resultRisposta->fetch_assoc()) {
                                echo "<p class='reply'><strong>Risposta:</strong> " . htmlspecialchars($rowRisposta['testo']) . "</p><br>";
                            } else {
                                echo "<div class='reply-box' id='reply-box-" . $commento['id'] . "'>";
                                echo "<input type='text' class='reply-input' id='reply-input-" . $commento['id'] . "' placeholder='Scrivi una risposta...'>";
                                echo "<button class='submit-reply' data-comment-id='" . $commento['id'] . "'>Invia</button>";
                                echo "</div><br>";
                            }
                            echo"</div>";
                        }
                    } else {
                        echo "<p>Nessun commento per questo progetto.</p>";
                    }
                    echo "</div>";  

                    // Mostra bottoni in base al tipo di progetto
                    if ($progetto['tipo'] === 'software') {
                        echo "<div class='project-buttons'>";
                        echo "<a href='../candidatura/inserisci_profilo.php?progetto=$nomeProgetto'>Inserisci Profilo</a>";
                        echo "<a href='../candidatura/gestisci_candidature.php?progetto=$nomeProgetto'>Gestisci Candidature</a>";
                        echo "</div>";
                    }
                    if ($progetto['tipo'] === 'hardware') {
                        echo "<div class='project-buttons'>";
                        echo "<a href='inserisci_componenti.php?progetto=$nomeProgetto'>Inserisci Componenti</a>";
                        echo "</div>";
                    }        
                    echo "</div></div>"; 
                }
            } else {
                echo "<p>Non hai ancora creato nessun progetto.</p>";
            }
            ?>
        </section>
    </div>

    <?php include_once realpath(__DIR__ . '/../includes/footer.php'); ?>

    <script>
        // Gestione invio risposte ai commenti via fetch AJAX
        document.querySelectorAll('.submit-reply').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const replyText = document.getElementById(`reply-input-${commentId}`).value;
                const formData = new FormData();
                
                formData.append('commentId', commentId);
                formData.append('testo', replyText);

                fetch('rispondi_commento.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if(data === 'success') {
                        location.reload(); // Ricarica la pagina per aggiornare la risposta
                    } else {
                        alert('Errore: ' + data);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>
