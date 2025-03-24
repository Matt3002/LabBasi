<?php
require 'config.php';

$emailSession = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
$esito = '';

// Se il form è stato inviato
if (isset($_POST['submit_commento']) && $emailSession) {
    $progetto = $_POST['progetto'];
    $testo = $_POST['commento'];
    $data = date('Y-m-d'); // Data odierna

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    // Richiama la stored procedure per inserire il commento
    $stmt = $conn->prepare("CALL AggiungiCommento(?, ?, ?, ?)");
    $stmt->bind_param("ssss", $emailSession, $progetto, $data, $testo);

    if ($stmt->execute()) {
        $esito = "<p class='success'>Commento aggiunto con successo!</p>";
        // Aggiorna la pagina per mostrare il commento senza ricaricare tutto
        header("Location: visualizza_Progetti.php");
        exit();
    } else {
        $esito = "<p class='error'>Errore nell'aggiunta del commento.</p>";
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
    <title>Bostarter</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/options.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .success { color: green; }
        .error { color: red; }
        .comment-section {
            display: none;
            margin-top: 10px;
        }
    </style>
    <script>
        function toggleMenu() {
            let sidebar = document.getElementById("sidebar");
            let menuButton = document.querySelector(".menu");
            if (sidebar.style.left === "-250px" || sidebar.style.left === "") {
                sidebar.style.left = "0px";
                menuButton.style.left = "280px";
            } else {
                sidebar.style.left = "-250px";
                menuButton.style.left = "30px";
            }
        }

        function toggleCommentSection(progetto) {
            let commentSection = document.getElementById("comment-section-" + progetto);
            if (commentSection.style.display === "none" || commentSection.style.display === "") {
                commentSection.style.display = "block";
            } else {
                commentSection.style.display = "none";
            }
        }
    </script>
</head>
<body>

    <div class="menu" onclick="toggleMenu()">☰</div>
    <header><h1><a href="dashboard/dashboard.php">Bostarter</a></h1></header>

    <div id="sidebar" class="sidebar">
        <a href="inserisci_Skill.php" onclick="toggleMenu()">Inserisci Skill</a>
        <a href="visualizza_Progetti.php" onclick="toggleMenu()">Progetti Disponibili</a>
        <a href="finanzia_Progetto.php" onclick="toggleMenu()">Finanzia un Progetto</a>
    </div>

    <div class="content">
        <section>
            <h2>Progetti Disponibili</h2>
            <?php echo $esito; ?>
            <?php
                require 'config.php';

                $conn = new mysqli($host, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("Connessione fallita: " . $conn->connect_error);
                }

                $sql = "CALL VisualizzaProgetti()";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $progettoNome = htmlspecialchars($row['nome']);
                        echo "<div class='project-card'>";
                        echo "<img src='images/default_project.jpg' alt='Immagine progetto'>";
                        echo "<div class='project-info'>";
                        echo "<h3>" . $progettoNome . "</h3>";
                        echo "<p>" . $row['descrizione'] . "</p>";
                        echo "<p>Data Inserimento: " . $row['data_Inserimento'] . "</p>";
                        echo "<p>Budget: " . $row['budget'] . "</p>";
                        echo "<p>Data Limite: " . $row['data_Limite'] . "</p>";
                        echo "<p>Stato: " . $row['stato'] . "</p>";
                        echo "<p>Tipo: " . $row['tipo'] . "</p>";
                        echo "<p>Email Creatore: " . $row['email_Creatore'] . "</p>";
                        echo "<div class='project-actions'>";
                        echo "<button onclick=\"toggleCommentSection('$progettoNome')\">Lascia un commento</button>";
                        if($row['tipo'] == "software") {
                            echo "<button onclick=\"location.href='candidati_Progetto.php?progetto=" . urlencode($row['nome']) . "'\">Invia candidatura</button>";
                        }
                        echo "<button onclick=\"location.href='finanzia_Progetto.php?progetto=" . urlencode($row['nome']) . "'\">Finanzia Progetto</button>";
                        echo "</div>";

                        // Sezione commento nascosta di default
                        echo "<div id='comment-section-$progettoNome' class='comment-section'>";
                        echo "<form method='post'>";
                        echo "<textarea name='commento' placeholder='Scrivi il tuo commento qui...' required></textarea>";
                        echo "<input type='hidden' name='progetto' value='$progettoNome'>";
                        echo "<button type='submit' name='submit_commento'>Invia</button>";
                        echo "</form>";
                        echo "</div>";

                        echo "</div></div>";
                    }
                } else {
                    echo "<p>Nessun progetto disponibile</p>";
                }
                $conn->close();
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
