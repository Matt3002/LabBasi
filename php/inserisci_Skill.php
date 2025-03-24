<?php
session_start();
require 'config.php';

$emailSession = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
$skillOptions = '';
$hasAvailableSkills = false;
$esito = '';

if ($emailSession) {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    // Recupero delle skill disponibili
    $stmt = $conn->prepare("CALL VisualizzaSkillDisponibili(?)");
    $stmt->bind_param("s", $emailSession);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $skillOptions = "<option disabled>Tutte le skill sono già state aggiunte</option>";
    } else {
        $hasAvailableSkills = true;
        while ($row = $result->fetch_assoc()) {
            $competenza = htmlspecialchars($row['competenza']);
            $skillOptions .= "<option value='{$competenza}'>{$competenza}</option>";
        }
    }

    $stmt->close();

    // Gestione form submit
    if (isset($_POST['submit']) && $hasAvailableSkills) {
        $competenza = $_POST['competenza'];
        $livello = $_POST['livello'];

        $stmt = $conn->prepare("CALL AggiungiSkillCurriculum(?, ?, ?)");
        $stmt->bind_param("ssi", $emailSession, $competenza, $livello);

        if ($stmt->execute()) {
            // Refresh automatico della pagina per aggiornare la lista
            header("Location:inserisci_Skill.php");
            exit();
        } else {
            $esito = "<p class='error'>Errore: la skill è già presente o il livello non è valido.</p>";
        }

        $stmt->close();
    }

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
            <?php echo $esito; ?>

            <?php if ($emailSession): ?>
                <form method="post">
                    <label for="competenza">Skill disponibili</label>
                    <select name="competenza" id="competenza" required <?php if (!$hasAvailableSkills) echo 'disabled'; ?>>
                        <option value="">-- Seleziona una competenza --</option>
                        <?php echo $skillOptions; ?>
                    </select>

                    <label for="livello">Livello</label>
                    <select name="livello" id="livello" required>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select><br>

                    <button type="submit" name="submit" <?php if (!$hasAvailableSkills) echo 'disabled'; ?>>
                        Aggiungi Skill
                    </button>
                </form>
            <?php else: ?>
                <p class="error">Errore: sessione utente non attiva. Effettua il login.</p>
            <?php endif; ?>
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
