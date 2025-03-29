<?php
session_start();
require '../config.php';

$emailSession = $_SESSION['user_email'] ?? null;
$skillOptions = '';
$hasAvailableSkills = false;
$esito = '';

if ($emailSession) {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($host, $username, $password, $dbname);

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
                header("Location: inserisci_Skill.php");
                exit();
            }
            $stmt->close();
        }

        $conn->close();
    } catch (mysqli_sql_exception $e) {
        if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
            $esito = "<p class='error'>" . htmlspecialchars($matches[1]) . "</p>";
        } else {
            $esito = "<p class='error'>Errore MySQL: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } catch (Exception $e) {
        $esito = "<p class='error'>Errore generico: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bostarter</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/options.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

    <header><h1><a href="../dashboard/dashboard.php">Bostarter</a></h1></header>

    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>

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
