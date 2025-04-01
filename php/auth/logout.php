<?php
session_start();
//Rimuove tutte le variabili di sessione
session_unset();
// Distrugge la sessione corrente
session_destroy();
header("Location: ../../index.html");
exit();
?>
