<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: welcome.php');
    exit;
}
?>