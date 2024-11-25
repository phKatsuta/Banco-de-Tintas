<?php
session_start();
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verifica o usuário
    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE usuario_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['senha_hash'])) {
        // Define a sessão
        $_SESSION['usuario_id'] = $user['id'];

        // Obtém os tipos de usuário
        $stmt = $pdo->prepare("SELECT tipo FROM Usuario_Tipos WHERE usuario_id = ?");
        $stmt->execute([$user['id']]);
        $user_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $_SESSION['user_types'] = $user_types;

        // Redireciona para a página inicial
        header("Location: index.php");
        exit;
    } else {
        $error = "Email ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        <br>
        <label>Senha:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
