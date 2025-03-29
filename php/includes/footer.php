<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<footer style="background-color: green; color: white; padding: 10px 0; position: fixed; bottom: 0; width: 100%;">
  <div style="max-width: 1200px; margin: auto; display: flex; justify-content: space-between; flex-wrap: wrap; text-align: center;">

    <!-- Colonna sinistra -->
    <div style="flex: 1; min-width: 200px;">
      <strong>Bostarter</strong>
    </div>

    <!-- Colonna centrale -->
    <div style="flex: 1; min-width: 200px;">
      <strong>Contattaci</strong><br>
      <small>email: admin123@gmail.com</small>
    </div>

    <!-- Colonna destra -->
    <div style="flex: 1; min-width: 200px;">
      <strong>Seguici</strong><br>
      <span style="font-size: 18px;">📘 📸 🎵</span>
    </div>
  </div>

  <div style="text-align: center; margin-top: 5px; font-size: 13px;">
    © 2025 Bostarter. Tutti i diritti riservati.
  </div>
</footer>
