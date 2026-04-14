<?php
// ============================================================
// FILE TEST KEAMANAN — UPLOAD VULNERABILITY CHECK
// Dibuat untuk membuktikan celah upload di MASSIPA
// HAPUS FILE INI SETELAH SELESAI TESTING!
// ============================================================

echo "<h2 style='color:red;font-family:monospace;'>⚠️ VULNERABILITY CONFIRMED!</h2>";
echo "<p style='font-family:monospace;'>File PHP ini berhasil diupload dan dieksekusi!</p>";
echo "<hr>";
echo "<b>Server:</b> " . $_SERVER['SERVER_NAME'] . "<br>";
echo "<b>Path file ini:</b> " . __FILE__ . "<br>";
echo "<b>Waktu eksekusi:</b> " . date('Y-m-d H:i:s') . "<br>";
echo "<b>PHP Version:</b> " . phpversion() . "<br>";
echo "<hr>";
echo "<p style='color:red;'>Ini artinya attacker bisa upload dan eksekusi kode PHP apapun di server ini!</p>";
