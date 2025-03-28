<?php
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $descrizione = $_POST["descrizione"];
    $nome_progetto = $_POST["nome_progetto"];

    // Lettura del file immagine
    $foto_temp = $_FILES["foto"]["tmp_name"];
    $foto_blob = file_get_contents($foto_temp);

    $stmt = $conn->prepare("CALL InserisciReward(?, ?, ?)");
    $stmt->bind_param("sss", $descrizione, $foto_blob, $nome_progetto);

    if ($stmt->execute()) {
        echo "Reward inserita con successo!";
    } else {
        echo "Errore: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- FORM HTML -->
<form method="post" action="" enctype="multipart/form-data">
    <label>Descrizione Reward:</label><br>
    <textarea name="descrizione" required></textarea><br>

    <label>Immagine (file):</label><br>
    <input type="file" name="foto" accept="image/*" required><br>

    <label>Nome Progetto:</label><br>
    <input type="text" name="nome_progetto" required><br>

    <input type="submit" value="Inserisci Reward">
</form>
