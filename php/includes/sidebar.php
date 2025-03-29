<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = "http://localhost/bostarter/LabBasi/php/";
?>

<!-- Bottone per aprire il menu -->
<div class="menu" onclick="toggleMenu()">☰</div>

<!-- Sidebar dinamica -->
<div id="sidebar" class="sidebar">
    <?php if ($_SESSION['user_role'] === 'Utente'): ?>
        <a href="<?= $basePath ?>skill/inserisci_Skill.php" onclick="toggleMenu()">Inserisci Skill</a>
        <a href="<?= $basePath ?>progetto/visualizza_Progetti.php" onclick="toggleMenu()">Progetti Disponibili</a>

    <?php elseif ($_SESSION['user_role'] === 'Creatore'): ?>
        <a href="<?= $basePath ?>skill/inserisci_Skill.php" onclick="toggleMenu()">Inserisci Skill</a>
        <a href="<?= $basePath ?>progetto/visualizza_Progetti.php" onclick="toggleMenu()">Progetti Disponibili</a>
        <a href="<?= $basePath ?>progetto/inserisci_Progetto.php" onclick="toggleMenu()">Inserisci Progetto</a>
        <a href="<?= $basePath ?>progetto/inserisci_reward.php" onclick="toggleMenu()">Inserisci Reward</a>
        <a href="<?= $basePath ?>progetto/miei_Progetti.php" onclick="toggleMenu()">I Tuoi Progetti</a>

    <?php elseif ($_SESSION['user_role'] === 'Amministratore'): ?>
        <a href="<?= $basePath ?>skill/inserisci_Skill.php" onclick="toggleMenu()">Inserisci Skill</a>
        <a href="<?= $basePath ?>progetto/visualizza_Progetti.php" onclick="toggleMenu()">Progetti Disponibili</a>
        <a href="<?= $basePath ?>skill/gestione_Competenze.php" onclick="toggleMenu()">Gestione Competenze</a>
    <?php endif; ?>
</div>

<!-- Script JS per toggle -->
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
<!-- Stile CSS della sidebar -->
<style>
    .menu { 
    position: fixed;
    top: 2vh; 
    left: 30px; 
    cursor: pointer; 
    font-size: 24px; 
    z-index: 2;
    color: green;
}
        
.sidebar { 
    position: fixed; 
    left: -250px; 
    top: 0; 
    width: 300px; 
    height: 100%; 
    background: white; 
    color: green; 
    padding: 20px; 
    transition: 0.3s; 
    z-index: 1;
}
        
.sidebar a { 
    display: block; 
    color: green; 
    text-decoration: none; 
    padding: 1vh 1vw 1vh; 
    max-width: fit-content;
}

.sidebar a:hover { 
    background: lightgreen; 
}
        
.sidebar .divider { 
    border-top: medium solid lightgreen; 
    margin: 5vh 0; 
    max-width: 200px;
}
</style>
