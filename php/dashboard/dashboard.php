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
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    
    <header><h1><a href="dashboard.php">Bostarter</a></h1></header>
    <?php include_once realpath(__DIR__ . '/../includes/sidebar.php'); ?>

    <section class="content">
        <div class="stats">
            <div class="stat-box">
                <h3>Top Creatori</h3>
                <p>
                <?php
                    require '../config.php';

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

    <footer id="footerBase">

        <div id="column">
            <h4 id="wpp">Bostarter</h4>

            <div id="contatti">
                <a href="mailto:valeria.sensini2@studio.unibo.it"><h4>Contattaci</h4></a>
                <h6>email: admin123@email.com </h6>
            </div>

            <div id="icon">
                <h4>Seguici</h4>
                <a href="#" style="margin-right: 2.4vw;"><i class="fab fa-facebook"></i></a>
                <span></span>
                <a href="#" style="margin-right: 2.4vw;"><i class="fab fa-instagram"></i></a>
                <span></span>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>

        <div id="diritti">
            <p>© 2025 Bostarter. Tutti i diritti riservati.</p>
        </div>

    </footer>
</body>
</html>