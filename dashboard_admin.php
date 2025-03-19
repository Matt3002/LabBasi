<?php
session_start();
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== "Amministratore") {
    header("Location: login.php");
    exit();
}

echo "<h1>Benvenuto, Amministratore!</h1>";
echo "<p>Email: " . $_SESSION['user_email'] . "</p>";
echo "<a href='logout.php'>Logout</a>";
?>
