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
    <header><h1><a href="dashboard/dashboard_creatore.php">Bostarter</a></h1></header>
    <div id="sidebar" class="sidebar">
        <a href="inserisci_Skill.php" onclick="toggleMenu()">Inserisci Skill</a>
        <a href="visualizza_Progetti.php" onclick="toggleMenu()">Progetti Disponibili</a>
        <a href="finanzia_Progetto.php" onclick="toggleMenu()">Finanzia un Progetto</a>
        <div class="divider"></div>
        <a href="inserisci_Progetto.php" onclick="toggleMenu()">Inserisci Progetto</a>
        <a href="#" onclick="toggleMenu()">Inserisci Reward</a>
        <a href="#" onclick="toggleMenu()">Rispondi ai commenti</a>
        <a href="#" onclick="toggleMenu()">Inserisci Profilo</a>
        <a href="#" onclick="toggleMenu()">Gestione Candidature</a>
    </div>

    <div class="content">
    <section>
            <h2>Inserisci un nuovo progetto</h2>
            <form method="post">
                <input type="text" name="nome" placeholder="Nome del progetto" required><br>
                <textarea name="descrizione" placeholder="Descrizione" required></textarea><br>
                <input type="date" name="data_inserimento" required>
                <input type="number" step="0.01" name="budget" placeholder="Budget" required><br>
                <input type="date" name="data_limite" required>
                <select name="stato">
                    <option value="aperto">aperto</option>
                    <option value="chiuso">chiuso</option>
                </select><br>
                <select name="tipo">
                    <option value="hardware">hardware</option>
                    <option value="software">software</option>
                </select>
                <input type="email" name="email_creatore" placeholder="Email del creatore" required><br>
                <button type="submit">Aggiungi Progetto</button>
            </form>
            <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    require 'config.php';

                    error_reporting(E_ALL);
                    ini_set('display_errors', 1);

                    $conn = new mysqli($host, $username, $password, $dbname);
                    if ($conn->connect_error) {
                        die("Connessione fallita: " . $conn->connect_error);
                    }
                    
                    $_POST["stato"] = strtolower(trim($_POST["stato"]));
                    $_POST["tipo"] = strtolower(trim($_POST["tipo"]));
                    
                    $stmt = $conn->prepare("CALL InserisciProgetto(?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssdssss", $_POST["nome"], $_POST["descrizione"], $_POST["data_inserimento"], $_POST["budget"], $_POST["data_limite"], $_POST["stato"], $_POST["tipo"], $_POST["email_creatore"]);
                    if ($stmt->execute()) {
                        echo "<p>Progetto inserito con successo!</p>";
                    } else {
                        echo "<p>Errore: " . $stmt->error . "</p>";
                    }
                    $stmt->close();
                    $conn->close();
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