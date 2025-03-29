<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/mongo_logger.php';

logEvento("Test log MongoDB diretto senza Composer");

echo "Log inserito!";
?>
