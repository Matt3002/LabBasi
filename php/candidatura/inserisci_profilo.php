<?php
// Avvia la sessione
session_start();

// Includi il file di configurazione per la connessione al database
require '../config.php';
require_once '../includes/mongo_logger.php';

// Verifica se l'utente è loggato
if (!isset($_SESSION['user_email'])) {
    die("<p class='error'>Errore: Devi essere loggato per accedere a questa pagina.</p>");
}

// Recupera il nome del progetto dall'URL
$nomeProgetto = $_GET['progetto'] ?? null;

if (!$nomeProgetto) {
    die("<p class='error'>Errore: Nome del progetto non specificato.</p>");
}

// Connessione al database
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// **Gestione dell'inserimento di un nuovo profilo**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profilo_da_aggiungere'])) {
    $idProfiloDaAggiungere = $_POST['profilo_da_aggiungere'];

    // Controlla se il profilo è già associato
    $stmtVerifica = $conn->prepare("SELECT COUNT(*) FROM Profilo_Software WHERE nome_Software = ? AND id_Profilo = ?");
    $stmtVerifica->bind_param("si", $nomeProgetto, $idProfiloDaAggiungere);
    $stmtVerifica->execute();
    $stmtVerifica->bind_result($count);
    $stmtVerifica->fetch();
    $stmtVerifica->close();

    if ($count == 0) {
        // Aggiungi il profilo al progetto
        $stmtInserisci = $conn->prepare("INSERT INTO Profilo_Software (nome_Software, id_Profilo) VALUES (?, ?)");
        $stmtInserisci->bind_param("si", $nomeProgetto, $idProfiloDaAggiungere);
        if ($stmtInserisci->execute()) {
            echo "<p class='success'>Profilo aggiunto con successo.</p>";
            logEvento("Profilo $idProfiloDaAggiungere aggiunto a progetto $nomeProgetto");
        } else {
            echo "<p class='error'>Errore nell'aggiunta del profilo.</p>";
        }
        $stmtInserisci->close();
    } else {
        echo "<p class='error'>Questo profilo è già associato al progetto.</p>";
    }
}

// **Recupera i profili già associati al progetto**
$stmt = $conn->prepare("SELECT id_Profilo FROM Profilo_Software WHERE nome_Software = ?");
$stmt->bind_param("s", $nomeProgetto);
$stmt->execute();
$result = $stmt->get_result();
$profili = [];

while ($row = $result->fetch_assoc()) {
    $idProfilo = $row['id_Profilo'];

    // Recupera il nome del profilo
    $stmtProfilo = $conn->prepare("SELECT nome FROM Profilo WHERE id = ?");
    $stmtProfilo->bind_param("i", $idProfilo);
    $stmtProfilo->execute();
    $resultProfilo = $stmtProfilo->get_result();

    if ($profilo = $resultProfilo->fetch_assoc()) {
        $nomeProfilo = $profilo['nome'];

        // Recupera le competenze e i livelli associati a questo profilo
        $stmtCompetenze = $conn->prepare("SELECT nome_competenza, livello FROM ProfiloSkill WHERE id_profilo = ?");
        $stmtCompetenze->bind_param("i", $idProfilo);
        $stmtCompetenze->execute();
        $resultCompetenze = $stmtCompetenze->get_result();

        $competenze = [];
        while ($rowComp = $resultCompetenze->fetch_assoc()) {
            $competenze[] = [
                'competenza' => $rowComp['nome_competenza'],
                'livello' => $rowComp['livello']
            ];
        }

        $profili[] = [
            'nome' => $nomeProfilo,
            'competenze' => $competenze
        ];

        $stmtCompetenze->close();
    }

    $stmtProfilo->close();
}

$stmt->close();

// **Recupera i profili disponibili (non ancora associati)**
$stmt = $conn->prepare("SELECT id, nome FROM Profilo WHERE id NOT IN (SELECT id_Profilo FROM Profilo_Software WHERE nome_Software = ?) ORDER BY nome");
$stmt->bind_param("s", $nomeProgetto);
$stmt->execute();
$resultProfiliDisponibili = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestisci Profili - <?php echo htmlspecialchars($nomeProgetto); ?></title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/options.css">
    <style>
        .error { color: red; }
        .success { color: green; }
        .profile-list {
            list-style-type: none;
            padding: 0;
        }
        .profile-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .competence-list {
            margin-top: 5px;
            padding-left: 15px;
        }
        .add-profile-form {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .add-profile-form select {
            padding: 5px;
            margin-right: 10px;
        }
        .add-profile-form button {
            padding: 5px 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .add-profile-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <header>
        <h1><?php echo htmlspecialchars($nomeProgetto); ?></h1>
    </header>
    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>
    <div class="content">
        <section>
            <h2>Profili Associati</h2>
            <?php if (!empty($profili)): ?>
                <ul class="profile-list">
                    <?php foreach ($profili as $profilo): ?>
                        <li class="profile-item">
                            <h3><?php echo htmlspecialchars($profilo['nome']); ?></h3>
                            <p><strong>Competenze richieste:</strong></p>
                            <ul class="competence-list">
                                <?php foreach ($profilo['competenze'] as $competenza): ?>
                                    <li><?php echo htmlspecialchars($competenza['competenza']); ?> (Livello: <?php echo $competenza['livello']; ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nessun profilo associato a questo progetto.</p>
            <?php endif; ?>
        </section>

        <!-- Sezione per aggiungere un nuovo profilo -->
        <section class="add-profile-form">
            <h2>Aggiungi un Nuovo Profilo</h2>
            <form method="POST">
                <label for="profilo_da_aggiungere">Seleziona un profilo:</label>
                <select name="profilo_da_aggiungere" id="profilo_da_aggiungere" required>
                    <?php while ($profilo = $resultProfiliDisponibili->fetch_assoc()): ?>
                        <option value="<?php echo $profilo['id']; ?>"><?php echo htmlspecialchars($profilo['nome']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Aggiungi Profilo</button>
            </form>
        </section>

    </div>

</body>
</html>
