<?php
require_once '../includes/verifica_gestor.php';

// Inicializa a variável para exibir mensagens
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Captura os dados do formulário
        $ra = trim($_POST['ra']);
        $nome = trim($_POST['nome']);
        $curso = trim($_POST['curso']);
        $email = trim($_POST['email']);

        // Validação básica dos dados
        if (empty($ra) || empty($nome) || empty($curso) || empty($email)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        // Geração da senha hash baseada no email
        $senha_hash = password_hash($email, PASSWORD_DEFAULT);

        // Início da transação
        $pdo->beginTransaction();

        // Inserção na tabela `Usuarios`
        $stmt_usuarios = $pdo->prepare("
            INSERT INTO Usuarios (usuario_nome, usuario_email, senha_hash, ativo) 
            VALUES (:nome, :email, :senha_hash, 1)
        ");
        $stmt_usuarios->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha_hash' => $senha_hash
        ]);

        // Captura o ID do usuário recém-inserido
        $usuario_id = $pdo->lastInsertId();

        // Inserção na tabela `Monitor`
        $stmt_monitor = $pdo->prepare("
            INSERT INTO Monitor (id_monitor, registro, curso) 
            VALUES (:id_monitor, :ra, :curso)
        ");
        $stmt_monitor->execute([
            ':id_monitor' => $usuario_id,
            ':ra' => $ra,
            ':curso' => $curso
        ]);

        // Inserção na tabela `Usuario_Tipos`
        $stmt_usuario_tipos = $pdo->prepare("
            INSERT INTO Usuario_Tipos (usuario_id, tipo) 
            VALUES (:usuario_id, 'Monitor'),
                    (:usuario_id, 'Doador')
        ");
        $stmt_usuario_tipos->execute([
            ':usuario_id' => $usuario_id
        ]);

        // Confirma a transação
        $pdo->commit();

        // Define mensagem de sucesso
        $message = '<div class="success">Monitor cadastrado com sucesso!</div>';
    } catch (Exception $e) {
        // Reverte a transação em caso de erro
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Define mensagem de erro
        $message = '<div class="error">Erro ao cadastrar monitor: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

include '../templates/header.php';
?>

<h2>Cadastrar Monitor</h2>
<?php if (!empty($message)) echo $message; ?>
<form method="post">
    <label for="ra">RA:</label>
    <input type="text" id="ra" name="ra" required><br><br>

    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" required><br><br>

    <label for="curso">Curso:</label>
    <select name="curso" id="curso" required>
        <option value="" disabled selected>--Selecione o curso--</option>
        <option value="ads">ADS - Análise e Desenvolvimento de Sistemas</option>
        <option value="cd">CD - Ciência de Dados</option>
        <option value="dc">DC - Defesa Cibernética</option>
        <option value="eve">EVE - Eventos</option>
        <option value="gam">GAM - Gestão Ambiental</option>
        <option value="gli">GLI - Gestão de Logística Integrada</option>
        <option value="gti">GTI - Gestão da TI</option>
        <option value="log">LOG - Logística</option>
        <option value="se">SE - Sistemas Embarcados</option>
    </select>
    <br><br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <button type="submit">Cadastrar</button>
</form>

<script type="text/javascript" src="../SCRIPT/script_cadastro.js"></script>
<script type="text/javascript" src="../SCRIPT/script.js"></script>
<?php include '../templates/footer.php'; ?>
