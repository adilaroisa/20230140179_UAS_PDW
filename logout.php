<?php
session_start();
$_SESSION = []; // Bersihkan data session
session_destroy();
setcookie(session_name(), '', time()-3600, '/'); // Hapus cookie dasar
header("Location: login.php");
exit;
?>