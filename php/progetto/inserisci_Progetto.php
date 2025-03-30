<?php
session_start();
require '../config.php';
require_once '../includes/mongo_logger.php';

$emailSession = $_SESSION['user_email'] ?? null;
$dataInserimento = date('Y-m-d'); // Data odierna
$esito = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $emailSession) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($host, $username, $password, $dbname);

        // Verifica se il nome del progetto esiste già
        $stmt = $conn->prepare("SELECT 1 FROM Progetto WHERE nome = ?");
        $stmt->bind_param("s", $_POST["nome"]);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $esito = "<p class='error'>Errore: Il nome del progetto è già in uso. Scegli un altro nome.</p>";
        } else {
            // Normalizza input
            $_POST["stato"] = strtolower(trim($_POST["stato"]));
            $_POST["tipo"] = strtolower(trim($_POST["tipo"]));

            $stmt->close();

            // Richiama la stored procedure
            $stmt = $conn->prepare("CALL InserisciProgetto(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdssss", $_POST["nome"], $_POST["descrizione"], $dataInserimento, $_POST["budget"], $_POST["data_limite"], $_POST["stato"], $_POST["tipo"], $emailSession);
            $stmt->execute();
            logEvento("Creato nuovo progetto: " . $_POST["nome"]);
            $esito = "<p class='success'>Progetto inserito con successo!</p>";
        }

        $stmt->close();
        $conn->close();
    } catch (mysqli_sql_exception $e) {
        if (preg_match("/Errore: (.+)$/", $e->getMessage(), $matches)) {
            $esito = "<p class='error'>" . htmlspecialchars($matches[1]) . "</p>";
        } else {
            $esito = "<p class='error'>Errore durante l'inserimento del progetto.</p>";
        }
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
    
    <header><h1><a href="../dashboard/dashboard.php">Bostarter</a></h1></header>
    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>

    <div class="content">
        <section>
            <h2>Inserisci un nuovo progetto</h2>
            <?php echo $esito; ?>
            <form method="post">
                <label for="nome">Nome del progetto</label>
                <input type="text" name="nome" placeholder="Nome del progetto" required><br>

                <label for="descrizione">Descrizione</label>
                <textarea name="descrizione" placeholder="Descrizione" required></textarea><br>

                <label>Data di Inserimento</label>
                <input type="text" value="<?php echo $dataInserimento; ?>" disabled><br>

                <label for="budget">Budget</label>
                <input type="number" step="0.01" name="budget" placeholder="Budget" required><br>

                <label for="data_limite">Data Limite</label>
                <input type="date" name="data_limite" required><br>

                <label for="stato">Stato</label>
                <select name="stato">
                    <option value="aperto">Aperto</option>
                    <option value="chiuso">Chiuso</option>
                </select><br>

                <label for="tipo">Tipo</label>
                <select name="tipo">
                    <option value="hardware">Hardware</option>
                    <option value="software">Software</option>
                </select><br>

                <label>Email Creatore</label>
                <input type="text" value="<?php echo $emailSession; ?>" disabled><br>

                <button type="submit">Aggiungi Progetto</button>
            </form>
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
