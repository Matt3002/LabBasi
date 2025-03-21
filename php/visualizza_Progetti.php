<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bostarter</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/options.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
            <?php
                require 'config.php';

                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                    $conn = new mysqli($host, $username, $password, $dbname);
                    if ($conn->connect_error) {
                        die("Connessione fallita: " . $conn->connect_error);
                    }
                $sql = "CALL VisualizzaProgetti()";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='project-card'>";
                        echo "<img src='images/default_project.jpg' alt='Immagine progetto'>"; //modifica procedura per prendere foto
                        echo "<div class='project-info'>";
                        echo "<h3>" . $row['nome'] . "</h3>";
                        echo "<p>" . $row['descrizione'] . "</p>";
                        echo "<p>Data Inserimento: " . $row['data_Inserimento'] . "</p>";
                        echo "<p>Budget: " . $row['budget'] . "</p>";
                        echo "<p>Data Limite: " . $row['data_Limite'] . "</p>";
                        echo "<p>Stato: " . $row['stato'] . "</p>";
                        echo "<p>Tipo: " . $row['tipo'] . "</p>";
                        echo "<p>Email Creatore: " . $row['email_Creatore'] . "</p>";
                        echo "<div class='project-actions'>";
                        echo "<button>Lascia un commento</button>";
                        echo "<button>Invia candidatura</button>";
                        echo "</div></div></div>";
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
                <span></span>
                <a href="#" style="margin-right: 2.4vw;"><i class="fab fa-instagram"></i></a>
                <span></span>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>

        <div id="diritti">
            <p>© 2025 Bostarter. Tutti i diritti riservati.</p>
        </div>

    </footer>
</body>
</html>