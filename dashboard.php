<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

echo "<h1>Benvenuto alla piattaforma!</h1>";
echo "<p>Email: " . $_SESSION['user_email'] . "</p>";
echo "<a href='logout.php'>Logout</a>";
?>
