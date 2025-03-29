<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require '../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$emailSession = $_SESSION['user_email'] ?? null;
$esito = '';

if (isset($_POST['submit_commento']) && $emailSession) {
    try {
        $progetto = $_POST['progetto'];
        $testo = $_POST['commento'];
        $data = date('Y-m-d');

        $conn = new mysqli($host, $username, $password, $dbname);

        $stmt = $conn->prepare("CALL AggiungiCommento(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $emailSession, $progetto, $data, $testo);

        if ($stmt->execute()) {
            header("Location: visualizza_Progetti.php");
            exit();
        }

    } catch (mysqli_sql_exception $e) {
        if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
            $esito = "<p class='error'>" . htmlspecialchars($matches[1]) . "</p>";
        } else {
            $esito = "<p class='error'>Errore nell'aggiunta del commento.</p>";
        }
    } finally {
        $stmt?->close();
        $conn?->close();
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
        .comment-section {
            display: none;
            margin-top: 10px;
        }
    </style>
    <script>
        function toggleCommentSection(progetto) {
            let commentSection = document.getElementById("comment-section-" + progetto);
            commentSection.style.display = commentSection.style.display === "block" ? "none" : "block";
        }
    </script>
</head>
<body>

<?php include_once realpath(__DIR__ . '/../includes/header.php'); ?>
<?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>

<div class="content">
    <section>
        <h2>Progetti Disponibili</h2>
        <?php echo $esito; ?>
        <?php
        try {
            $conn = new mysqli($host, $username, $password, $dbname);
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
                    if ($row['tipo'] == "software") {
                        echo "<button onclick=\"location.href='../candidatura/visualizza_Profili.php?nome_progetto=" . urlencode($progettoNome) . "'\">Invia candidatura</button>";
                    }
                    echo "<button onclick=\"location.href='../finanziamento/finanzia_progetto.php?nome_progetto=" . urlencode($row['nome']) . "'\">Finanzia Progetto</button>";
                    echo "</div>";

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
        } catch (mysqli_sql_exception $e) {
            echo "<p class='error'>Errore nel caricamento dei progetti: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </section>
</div>

<?php include_once realpath(__DIR__ . '/../includes/footer.php'); ?>

</body>
</html>
