<?php
// Inclui o arquivo de conexão
include 'conexao.php';

// Receber dados do formulário
$email = $_POST['email'];
$senha = $_POST['senha'];

// Preparar a consulta SQL
$sql = "SELECT * FROM Monitor WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($senha, $row['senha_hash'])) {
        // Login bem-sucedido
        session_start();
        $_SESSION['usuario_logado'] = $row['id_monitor'];
        header("Location: sucesso.php"); // Redirecionar para a página principal
    } else {
        echo "Senha incorreta.";
    }
} else {
    echo "Email não encontrado.";
}

$stmt->close();
$conn->close();