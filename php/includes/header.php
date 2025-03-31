<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$basePath = "http://localhost/bostarter/LabBasi/php/";
?>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../config.php"; 
$conn = new mysqli($host, $username, $password, $dbname);

$nickname = "Utente";

if (isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];

    $stmt = $conn->prepare("SELECT nickname FROM Utente WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($nicknameDb);

    if ($stmt->fetch()) {
        $nickname = $nicknameDb;
    }

    $stmt->close();
}
?>
<header style="background-color: green; color: white !important; padding: 20px; text-align: center;">
  <h1>Benvenuto in Bostarter, <?php echo htmlspecialchars($nickname); ?></h1>
 <a href="<?= $basePath ?>dashboard/dashboard.php">Torna alla home</a>
</header>
