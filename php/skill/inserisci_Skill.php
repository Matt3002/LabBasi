<?php
session_start();
require '../config.php';
require_once '../includes/mongo_logger.php';

$emailSession = $_SESSION['user_email'] ?? null;
$skillOptions = '';
$hasAvailableSkills = false;
$esito = '';
$curriculumTable = ''; 

if ($emailSession) {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($host, $username, $password, $dbname);

        $stmt = $conn->prepare("CALL VisualizzaSkillCurriculum(?)");
        $stmt->bind_param("s", $emailSession);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $curriculumTable .= "<table class='curriculum-table'>";
            $curriculumTable .= "<thead><tr><th>Skill</th><th>Livello</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                $skill = htmlspecialchars($row['competenza']);
                $livello = (int)$row['livello'];
                $curriculumTable .= "<tr><td>{$skill}</td><td>{$livello}</td></tr>";
            }
            $curriculumTable .= "</tbody></table>";
        } else {
            $curriculumTable = "<p>Non hai ancora aggiunto nessuna skill al tuo curriculum.</p>";
        }
        $stmt->close();
        $conn->next_result();

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
            logEvento("L'utente $emailSession ha aggiunto la competenza $competenza di livello $livello al proprio curriculum");

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
        .curriculum-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .curriculum-table th, .curriculum-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .curriculum-table th {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>

    <?php include_once realpath(__DIR__ . '/../includes/header.php'); ?>

    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>

    <div class="content">
        <section class="form-section">
            <h3>Il tuo curriculum</h3>
            <?php echo $curriculumTable; ?>
        </section>
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

    <?php include_once realpath(__DIR__ . '/../includes/footer.php'); ?>


</body>
</html>
