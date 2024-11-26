<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $confirmacao = $_POST['confirmText'];

    if (strtoupper($confirmacao) === 'EXCLUIR') {
        // Remove as permissões
        $sql = "DELETE FROM Usuario_Tipos WHERE usuario_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);

        // Remove o usuário
        $sql = "DELETE FROM Usuarios WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);

        session_destroy(); // Finaliza a sessão
        header("Location: index.php");
        exit;
    } else {
        echo "Confirmação inválida.";
    }
}
?>

<h3>Excluir Conta</h3>
<form method="POST" action="excluir_conta_action.php">
    <label for="confirmacao">Digite "EXCLUIR" para confirmar:</label>
    <input type="text" name="confirmacao" id="confirmacao" required>
    <button type="submit">Excluir Conta</button>
</form>