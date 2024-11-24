<?php
function check_access($type) {
    session_start();
    if (!isset($_SESSION['usuario_id']) || !in_array($type, $_SESSION['user_types'])) {
        header("Location: ../login.php");
        exit;
    }
}
?>
