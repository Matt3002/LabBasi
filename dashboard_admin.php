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
    <link rel="stylesheet" href="dashboard.css">
    <script>
        function toggleMenu() {
            let sidebar = document.getElementById("sidebar");
            let menuButton = document.querySelector(".menu");
            if (sidebar.style.left === "-250px" || sidebar.style.left === "") {
                sidebar.style.left = "0px";
                menuButton.style.left = "280px";
            } else {
                sidebar.style.left = "-250px";
                menuButton.style.left = "30px";
            }
        }
    </script>
</head>
<body>
    
    <div class="menu" onclick="toggleMenu()">☰</div>
    <header><h1><a href="dashboard.php">Bostarter</a></h1></header>
    <div id="sidebar" class="sidebar">
        <a href="#" onclick="toggleMenu()">Inserisci Skill</a>
        <a href="#" onclick="toggleMenu()">Progetti Disponibili</a>
        <a href="#" onclick="toggleMenu()">Finanzia un Progetto</a>
    <!--- metti funzioni admin--->
    </div>

    <section class="content">
        <div class="stats">
            <div class="stat-box">
                <h3>Top Creatori</h3>
                <p>
                <?php
                    require 'config.php';

                    error_reporting(E_ALL);
                    ini_set('display_errors', 1);

                        $conn = new mysqli($host, $username, $password, $dbname);
                        if ($conn->connect_error) {
                            die("Connessione fallita: " . $conn->connect_error);
                        }
                        $sql = "SELECT * FROM classificacreatori LIMIT 3";
                        $result = $conn->query($sql);
                        if (!$result) {
                            die("Errore nella query: " . $conn->error);
                        }
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo $row["nickname"] . " - " . $row["affidabilita"] . "<br>";
                            }
                        } else {
                            echo "Nessun dato disponibile";
                        }
                        $conn->close();
                    ?>
                </p>
            </div>
            <div class="stat-box">
                <h3>Top 3 Progetti Vicini al Completamento</h3>
                <p>
                <?php
                        $conn = new mysqli($host, $username, $password, $dbname);
                        if ($conn->connect_error) {
                            die("Connessione fallita: " . $conn->connect_error);
                        }
                        
                        $sql = "SELECT * FROM progettivicinicompletamento LIMIT 3";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo $row["nome"] . " - mancano ancora: " . $row["differenza"] . "€<br>";
                            }
                        } else {
                            echo "Nessun dato disponibile";
                        }
                        $conn->close();
                    ?>
                </p>
            </div>
            <div class="stat-box">
                <h3>Top 3 Finanziatori</h3>
                <p>
                <?php
                    
                        $conn = new mysqli($host, $username, $password, $dbname);
                        if ($conn->connect_error) {
                            die("Connessione fallita: " . $conn->connect_error);
                        }
                        $sql = "SELECT * FROM classificafinanziatori LIMIT 3";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo $row["nickname"] . " - " . $row["totale_finanziato"] . "€<br>";
                            }
                        } else {
                            echo "Nessun dato disponibile";
                        }
                        $conn->close();
                    
                    ?>
                </p>
            </div>
        </div>
    </section>
</body>
</html>