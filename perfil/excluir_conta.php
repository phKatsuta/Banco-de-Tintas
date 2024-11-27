<?php
require_once '../includes/gera_menu.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $confirmacao = $_POST['confirmacao'];

    if (strtoupper($confirmacao) === 'EXCLUIR') {
        // Inicia a transação para garantir consistência
        $pdo->beginTransaction();

        try {
            // Remove as permissões
            $sql = "DELETE FROM Usuario_Tipos WHERE usuario_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id]);

            // Remove o usuário
            $sql = "DELETE FROM Usuarios WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id]);

            // Remove a organização, se houver
            $sql = "DELETE FROM Organizacao WHERE id_organizacao = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id]);

            // Finaliza a sessão
            session_destroy();

            // Comita a transação
            $pdo->commit();

            // Redireciona para a página inicial após a exclusão
            header("Location: " . BASE_URL . "index.php");
            exit;

        } catch (Exception $e) {
            // Em caso de erro, reverte a transação
            $pdo->rollBack();
            echo "Ocorreu um erro ao excluir sua conta. Tente novamente." . $e->getMessage();
        }
    } else {
        $erro = "Confirmação inválida. Por favor, digite 'EXCLUIR' para confirmar.";
    }
}
?>

<h3>Excluir Conta</h3>

<?php if (isset($erro)) : ?>
    <p style="color: red;"><?= htmlspecialchars($erro); ?></p>
<?php endif; ?>
<h4>ATENÇÃO!</h4>
<p>Ao excluir sua conta, todos os seus dados serão permanentemente removidos do nosso sistema</p>

<form method="POST" action="">
    <label for="confirmacao">Digite "EXCLUIR" para confirmar a exclusão de sua conta:</label><br>
    <input type="text" name="confirmacao" id="confirmacao" required><br>
    <button type="submit">Excluir Conta</button>
</form>

<p>Se você não deseja excluir sua conta, <a href="../index.php">clique aqui</a> para voltar para a página inicial.</p>
