<?php
/*session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

echo "<h1>Benvenuto alla piattaforma!</h1>";
echo "<p>Email: " . $_SESSION['user_email'] . "</p>";
echo "<a href='logout.php'>Logout</a>";*/
?>
 
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bostarter</title>
    <style>
        
    </style>
    <script>
        function toggleMenu() {
            let sidebar = document.getElementById("sidebar");
            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-250px";
            } else {
                sidebar.style.left = "0px";
            }
        }
    </script>
</head>
<body>
    <div class="menu" onclick="toggleMenu()">☰</div>
    <header>Bostarter</header>
    <div id="sidebar" class="sidebar">
        <a href="#">Inserisci Skill</a>
        <a href="#">Progetti Disponibili</a>
        <a href="#">Finanzia un Progetto</a>
    </div>
    <div class="content">
        <div class="stats">
            <div class="stat-box">
                <h3>Top Creatori</h3>
                <p>Classifica degli utenti creatori</p>
            </div>
            <div class="stat-box">
                <h3>Progetti Vicini al Completamento</h3>
                <p>I primi 3 progetti più vicini al completamento</p>
            </div>
            <div class="stat-box">
                <h3>Top Finanziatori</h3>
                <p>Classifica dei primi 3 utenti per totale finanziamenti</p>
            </div>
        </div>
    </div>
</body>
</html>