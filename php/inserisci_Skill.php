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
    <section class="form-section">
            <h3>Aggiungi una Skill al Curriculum</h3>
            <form method="post">
                <input type="email" name="email" placeholder="Email Utente" required>
                <input type="text" name="competenza" placeholder="Nome Competenza" required> <!--- scegliere tra comp esistenti--->
                <select name="livello" required>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select><br>
                <button type="submit" name="submit">Aggiungi Skill</button>
            </form> <!---funziona ma va gestito caso in cui si stia inserendo una coppia comp-livello gia esistente per quell'utente--->
            <?php
                if (isset($_POST['submit'])) {
                    require 'config.php';

                    error_reporting(E_ALL);
                    ini_set('display_errors', 1);

                        $conn = new mysqli($host, $username, $password, $dbname);
                        if ($conn->connect_error) {
                            die("Connessione fallita: " . $conn->connect_error);
                        }
                    $email = $_POST['email'];
                    $competenza = $_POST['competenza'];
                    $livello = $_POST['livello'];
                    
                    $stmt = $conn->prepare("CALL AggiungiSkillCurriculum(?, ?, ?)");
                    $stmt->bind_param("ssi", $email, $competenza, $livello);
                    
                    if ($stmt->execute()) {
                        echo "<p>Skill aggiunta con successo!</p>";
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