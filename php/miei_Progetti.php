<?php
session_start();
require 'config.php';

$emailSession = $_SESSION['user_email'] ?? null;

if (!$emailSession) {
    die("<p class='error'>Errore: Devi essere loggato per vedere i tuoi progetti.</p>");
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$stmt = $conn->prepare("CALL VisualizzaProgettiCreati(?)");
$stmt->bind_param("s", $emailSession);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Progetti - Bostarter</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/options.css">
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
            border-left: 3px solid #007BFF;
        }
    </style>
</head>
<body>

    <div class="menu" onclick="toggleMenu()">☰</div>
    <header><h1><a href="dashboard/dashboard_creatore.php">Bostarter</a></h1></header>
    <div id="sidebar" class="sidebar">
        <a href="inserisci_Skill.php" onclick="toggleMenu()">Inserisci Skill</a>
        <a href="visualizza_Progetti.php" onclick="toggleMenu()">Progetti Disponibili</a>
        <a href="finanzia_Progetto.php" onclick="toggleMenu()">Finanzia un Progetto</a>
        <div class="divider"></div>
        <a href="inserisci_Progetto.php" onclick="toggleMenu()">Inserisci Progetto</a>
        <a href="miei_progetti.php" onclick="toggleMenu()">I Miei Progetti</a>
    </div>

    <div class="content">
        <section>
            <h2>I Miei Progetti</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($progetto = $result->fetch_assoc()) {
                    echo "<div class='project-card'>";
                    echo "<img src='images/default_project.jpg' alt='Immagine progetto'>";
                    echo "<div class='project-info'>";
                    echo "<h3>" . htmlspecialchars($progetto['nome']) . "</h3>";
                    echo "<p>" . htmlspecialchars($progetto['descrizione']) . "</p>";
                    echo "<p><strong>Budget:</strong> " . $progetto['budget'] . " €</p>";
                    echo "<p><strong>Data Limite:</strong> " . $progetto['data_Limite'] . "</p>";
                    echo "<p><strong>Stato:</strong> " . ucfirst($progetto['stato']) . "</p>";
                    // Recupera commenti con la stored procedure
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
                            $conn2 = new mysqli($host, $username, $password, $dbname);
                            if ($conn2->connect_error) {
                                die("Connessione fallita: " . $conn2->connect_error);
                            }
                            
                            $stmtRisposta = $conn2->prepare("SELECT testo FROM Risposta WHERE id_Commento = ?;");
                            $stmtRisposta->bind_param("i", $commento['id']);
                            $stmtRisposta->execute();
                            $resultRisposta = $stmtRisposta->get_result();
                            $stmtRisposta->close();
                            $conn2->close();

                                    // Controlla se il commento ha già una risposta
                                    if ($rowRisposta = $resultRisposta->fetch_assoc()) {
                                        echo "<p class='reply'><strong>Risposta:</strong> " . htmlspecialchars($rowRisposta['testo']) . "</p>";
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
                                
                    echo "</div></div>"; // Fine project-card
                }
            } else {
                echo "<p>Non hai ancora creato nessun progetto.</p>";
            }


            ?>
        </section>
    </div>

    <footer id="footerBase">
        <div id="column">
            <h4 id="wpp">Bostarter</h4>
            <div id="contatti">
                <a href="mailto:valeria.sensini2@studio.unibo.it"><h4>Contattaci</h4></a>
                <h6>email: admin123@email.com </h6>
            </div>
            <div id="icon">
                <h4>Seguici</h4>
                <a href="#" style="margin-right: 2.4vw;"><i class="fab fa-facebook"></i></a>
                <a href="#" style="margin-right: 2.4vw;"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        <div id="diritti">
            <p>© 2025 Bostarter. Tutti i diritti riservati.</p>
        </div>
    </footer>

</body>
</html>