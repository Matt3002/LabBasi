<?php
session_start();
include 'config.php'; // Connessione al database

if (!isset($_SESSION['email'])) {
    die("Accesso negato. Devi effettuare il login.");
}

if (!isset($_GET['nome_progetto'])) {
    die("Errore: nome progetto mancante.");
}

$nome_progetto = $_GET['nome_progetto'];

$stmt = $conn->prepare("
    SELECT 
        PS.nome_Software AS nome_progetto, 
        P.id AS id_profilo, 
        P.nome AS nome_profilo,
        PK.nome_Competenza, 
        PK.livello
    FROM Profilo_Software PS
    JOIN Profilo P ON PS.id_Profilo = P.id
    LEFT JOIN ProfiloSkill PK ON P.id = PK.id_Profilo
    WHERE PS.nome_Software = ?
");
$stmt->bind_param("s", $nome_progetto);
$stmt->execute();
$result = $stmt->get_result();

$profili = [];
while ($row = $result->fetch_assoc()) {
    $profili[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profili richiesti per <?php echo htmlspecialchars($nome_progetto); ?></title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Profili richiesti per il progetto: <?php echo htmlspecialchars($nome_progetto); ?></h2>
    
    <table>
        <tr>
            <th>Nome Profilo</th>
            <th>Competenza Richiesta</th>
            <th>Livello</th>
        </tr>
        <?php if (empty($profili)): ?>
            <tr><td colspan="3">Nessun profilo trovato per questo progetto.</td></tr>
        <?php else: ?>
            <?php foreach ($profili as $profilo): ?>
                <tr>
                    <td><?php echo htmlspecialchars($profilo['nome_profilo']); ?></td>
                    <td><?php echo htmlspecialchars($profilo['nome_Competenza'] ?? 'Nessuna competenza specificata'); ?></td>
                    <td><?php echo htmlspecialchars($profilo['livello'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <br>
    <a href="dashboard.php">Torna alla Dashboard</a>
</body>
</html>
