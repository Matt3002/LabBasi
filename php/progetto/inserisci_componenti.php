<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_email'])) {
    die("Accesso negato.");
}

$nomeProgetto = $_GET['progetto'] ?? null;

if (!$nomeProgetto) {
    die("Nome progetto non specificato.");
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Messaggi da azioni precedenti
$successMsg = $_SESSION['success'] ?? '';
$errorMsg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Componenti già inserite nel progetto
$stmt = $conn->prepare("SELECT LC.nome_Componente, C.descrizione, C.prezzo, LC.quantita
                        FROM Lista_Componenti LC
                        JOIN Componente C ON LC.nome_Componente = C.nome
                        WHERE LC.nome_Progetto = ?");
$stmt->bind_param("s", $nomeProgetto);
$stmt->execute();
$componentiProgetto = $stmt->get_result();
$stmt->close();

// Lista di tutte le componenti disponibili
$componentiDisponibili = $conn->query("SELECT nome FROM Componente");

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Componenti - <?= htmlspecialchars($nomeProgetto) ?></title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/options.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .success { color: green; }
        .error { color: red; }
        .container {
            padding-top: 200px;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }


        table { width: 80%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { margin-top: 20px; }
        input[type="number"], select {
            padding: 5px;
            margin-right: 10px;
        }
        button {
            padding: 6px 12px;
            background-color:  #28a745;
            color: white;
            border: none;
            border-radius: 5px;
        }
        button:hover {
            background-color: #218838;
        }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #218838;
        }
    </style>
</head>
<body>
    <?php include_once realpath(__DIR__ . '/../includes/header.php'); ?>
    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>
    <div class="container">
    <h2>Componenti del progetto: <?= htmlspecialchars($nomeProgetto) ?></h2>

    <?php if ($successMsg): ?>
        <p class="success"><?= htmlspecialchars($successMsg) ?></p>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
    <?php endif; ?>

    <?php if ($componentiProgetto->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Componente</th>
                    <th>Descrizione</th>
                    <th>Prezzo (€)</th>
                    <th>Quantità</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($comp = $componentiProgetto->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($comp['nome_Componente']) ?></td>
                        <td><?= htmlspecialchars($comp['descrizione']) ?></td>
                        <td><?= number_format($comp['prezzo'], 2, ',', '.') ?></td>
                        <td><?= $comp['quantita'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nessuna componente ancora associata a questo progetto.</p>
    <?php endif; ?>

    <form action="aggiungi_componente.php" method="POST">
        <input type="hidden" name="nome_progetto" value="<?= htmlspecialchars($nomeProgetto) ?>">
        <label for="nome_componente">Aggiungi Componente:</label>
        <select name="nome_componente" id="nome_componente" required>
            <option value="">-- Seleziona --</option>
            <?php while ($comp = $componentiDisponibili->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($comp['nome']) ?>"><?= htmlspecialchars($comp['nome']) ?></option>
            <?php endwhile; ?>
        </select>
        <label for="quantita">Quantità:</label>
        <input type="number" name="quantita" min="1" value="1" required>
        <button type="submit">Aggiungi</button>
    </form>

    <a class="back-link" href="miei_progetti.php">← Torna ai tuoi progetti</a>
    </div>
    <?php include_once realpath(__DIR__ . '/../includes/footer.php'); ?>

</body>
</html>
