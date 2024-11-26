<?php
require_once __DIR__ . '/includes/config.php'; // Inclui o arquivo de configuração com BASE_URL
session_start();
session_destroy();

// Redireciona para a página inicial usando BASE_URL
header("Location: " . BASE_URL . "index.php");
exit();