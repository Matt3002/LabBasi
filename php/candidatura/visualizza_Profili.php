<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$emailSession = $_SESSION['user_email'] ?? null;
if (!$emailSession) {
    die("Accesso negato. Devi effettuare il login.");
}

if (!isset($_GET['nome_progetto'])) {
    die("Errore: nome_progetto non fornito.");
}

$nome_progetto = $_GET['nome_progetto'];
$profili = [];

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    $stmt = $conn->prepare("
        SELECT 
            P.id AS id_profilo,
            P.nome AS nome_profilo, 
            GROUP_CONCAT(CONCAT(PK.nome_competenza, ' di livello ', PK.livello) 
                         ORDER BY PK.nome_competenza ASC 
                         SEPARATOR '; ') AS competenze
        FROM Profilo P
        JOIN Profilo_Software PS ON P.id = PS.id_profilo
        LEFT JOIN ProfiloSkill PK ON P.id = PK.id_profilo
        WHERE PS.nome_Software = ?
        GROUP BY P.id, P.nome
    ");
    $stmt->bind_param("s", $nome_progetto);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $profili[] = $row;
    }

    $stmt->close();
    $conn->close();

} catch (mysqli_sql_exception $e) {
    die("<p class='error'>Errore nel recupero dei profili: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profili richiesti per <?php echo htmlspecialchars($nome_progetto); ?></title>
    <style>
        .container { width: 80%; margin: auto; }
        .profile-card { 
            border: 1px solid #ccc; 
            padding: 15px; 
            margin-bottom: 15px; 
            border-radius: 5px; 
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }
        h2 { text-align: center; }
        .profile-card h3 { margin-bottom: 5px; }
        .profile-card p { margin: 2px 0; }
        .profile-card button {
            margin-top: 10px;
            padding: 8px 12px;
            border: none;
            background-color: #28a745;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        .profile-card button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
 
    <div class="container">
        <?php
            if (isset($_SESSION['success'])) {
                echo "<p style='color: green; font-weight: bold;'>" . $_SESSION['success'] . "</p>";
                unset($_SESSION['success']); // Rimuove il messaggio dopo la visualizzazione
            }

            if (isset($_SESSION['error'])) {
                echo "<p style='color: red; font-weight: bold;'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']); // Rimuove il messaggio dopo la visualizzazione
            }
        ?>
    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>
    
        <h2>Profili richiesti per il progetto: <?php echo htmlspecialchars($nome_progetto); ?></h2>

        <?php if (empty($profili)): ?>
            <p>Non ci sono profili associati a questo progetto.</p>
        <?php else: ?>
            <?php foreach ($profili as $profilo): ?>
                <div class="profile-card">
                    <h3><?php echo htmlspecialchars($profilo['nome_profilo']); ?></h3>
                    <p><strong>Competenze richieste:</strong></p>
                    <ul>
                    <?php 
                        $competenze_raw = $profilo['competenze'] ?? '';
                        $competenze_array = array_filter(explode("; ", $competenze_raw)); // evita null e filtra stringhe vuote

                        if (empty($competenze_array)) {
                            echo "<li>Nessuna competenza richiesta.</li>";
                        } else {
                            foreach ($competenze_array as $competenza): ?>
                                <li><?php echo htmlspecialchars($competenza); ?></li>
                            <?php endforeach;
                        }
                    ?>

                    </ul>
                    <form action="invia_candidatura.php" method="POST">
                        <input type="hidden" name="nome_progetto" value="<?php echo htmlspecialchars($nome_progetto ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id_profilo" value="<?php echo htmlspecialchars($profilo['id_profilo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit">Invia Candidatura</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="../dashboard/dashboard.php">Torna alla Dashboard</a>
    </div>
   
</body>
</html>

