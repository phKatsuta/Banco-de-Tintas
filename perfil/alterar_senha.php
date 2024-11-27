<?php
require_once '../includes/gera_menu.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Lógica para processar a alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual = $_POST['senha_atual'];
    $novaSenha = $_POST['nova_senha'];
    $confirmarSenha = $_POST['confirmar_senha'];
    $usuarioId = $_SESSION['usuario_id'];

    if ($novaSenha !== $confirmarSenha) {
        $erro = "As senhas não coincidem.";
    } else {
        // Consulta ao banco para verificar a senha atual
        $stmt = $pdo->prepare("SELECT senha_hash FROM Usuarios WHERE id = ?");
        $stmt->execute([$usuarioId]);
        $senhaHash = $stmt->fetchColumn();

        if (!$senhaHash || !password_verify($senhaAtual, $senhaHash)) {
            $erro = "A senha atual está incorreta.";
        } else {
            // Atualiza a nova senha
            $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE Usuarios SET senha_hash = ? WHERE id = ?");
            if ($stmt->execute([$novaSenhaHash, $usuarioId])) {
                $sucesso = "Senha alterada com sucesso! Você será redirecionado para a página inicial em 3 segundos.";
                echo "<meta http-equiv='refresh' content='3;url=../index.php'>"; // Redireciona após 3 segundos
            } else {
                $erro = "Erro ao alterar a senha. Tente novamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha</title>
</head>

<body>
    <main>
        <h1>Alterar Senha</h1>
        <?php if (isset($erro)): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>
        <?php if (isset($sucesso)): ?>
            <p class="sucesso"><?= htmlspecialchars($sucesso) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="senha_atual">Senha Atual:</label>
            <input type="password" id="senha_atual" name="senha_atual" required><br>

            <label for="nova_senha">Nova Senha:</label>
            <input type="password" id="nova_senha" name="nova_senha" required><br>

            <label for="confirmar_senha">Confirmar Nova Senha:</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required><br>

            <button type="submit">Alterar Senha</button>
        </form>
        <a href="../index.php">Voltar para a página inicial</a>

    </main>
</body>

</html>