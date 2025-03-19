<?php
session_start();
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== "Creatore") {
    header("Location: login.php");
    exit();
}

echo "<h1>Benvenuto, Creatore!</h1>";
echo "<p>Email: " . $_SESSION['user_email'] . "</p>";
echo "<a href='logout.php'>Logout</a>";
?>
