<?php
require_once '../includes/gera_menu.php';
include '../includes/via_cep.php';
include '../includes/busca_cep.php';
include '../includes/function_buscarEnderecoViaCep.php';
// include '../includes/teste_via_cep.php'; // Teste de funcionalidade da API

// Variáveis para armazenar mensagens de erro/sucesso
$errors = [];
$success = "";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe e valida os dados do formulário
    $nome = trim($_POST["usuario_nome"]);
    $cep = trim($_POST["usuario_cep"]);
    // Campos marcados como readonly precisam ter valores definidos antes de serem enviados. Para garantir que sejam enviados mesmo quando o preenchimento automático falhar:
    $endereco = trim($_POST["usuario_endereco"] ?? ""); // readonly
    $endereco_num = trim($_POST["usuario_endereco_num"]);
    $endereco_complemento = trim($_POST["usuario_endereco_complemento"]);
    $bairro = trim($_POST["usuario_bairro"] ?? ""); // readonly
    $cidade = trim($_POST["usuario_cidade"] ?? ""); // readonly
    $estado = trim($_POST["usuario_estado"] ?? ""); // readonly
    $email = trim($_POST["usuario_email"]);
    $senha = $_POST["senha"];
    $confirma_senha = $_POST["confirma_senha"];
    $documento = trim($_POST["usuario_documento"]);
    $telefone = trim($_POST["telefone"]);
    $eh_empresa = isset($_POST["eh_empresa"]) ? 1 : 0;
    $tipo_organizacao = $eh_empresa ? trim($_POST["tipo_organizacao"]) : null;
    $area_atuacao = $eh_empresa ? trim($_POST["area_atuacao"]) : null;
    $tipos = isset($_POST["tipos"]) ? $_POST["tipos"] : [];

    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirma_senha) || empty($telefone)) {
        $errors[] = "Todos os campos obrigatórios devem ser preenchidos.";
    }
    if ($senha !== $confirma_senha) {
        $errors[] = "As senhas não coincidem.";
    }
    if (empty($tipos)) {
        $errors[] = "Você deve escolher pelo menos uma opção (Doar ou Receber).";
    }
    if (strlen($endereco_complemento) > 100) {
        $errors[] = "O complemento do endereço deve ter no máximo 100 caracteres.";
    }

    // Se não houver erros, insere no banco
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Insere na tabela Usuarios
            $sql_usuario = "INSERT INTO Usuarios (
                usuario_nome, usuario_cep, usuario_endereco, usuario_endereco_num, usuario_endereco_complemento, usuario_bairro, usuario_cidade, 
                usuario_estado, usuario_email, senha_hash, usuario_documento, telefone, eh_empresa
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql_usuario);
            $stmt->execute([$nome, $cep, $endereco, $endereco_num, $endereco_complemento, $bairro, $cidade, $estado, $email, $senha_hash, $documento, $telefone, $eh_empresa]);

            // Pega o ID do último usuário inserido
            $usuario_id = $pdo->lastInsertId();

            // Insere na tabela Usuario_Tipos
            $sql_tipo = "INSERT INTO Usuario_Tipos (usuario_id, tipo) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql_tipo);
            foreach ($tipos as $tipo) {
                $stmt->execute([$usuario_id, $tipo]);
            }

            // Se for organização, insere na tabela Organizacao
            if ($eh_empresa) {
                $sql_organizacao = "INSERT INTO Organizacao (id_organizacao, tipo_organizacao, area_atuacao) 
                                    VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql_organizacao);
                $stmt->execute([$usuario_id, $tipo_organizacao, $area_atuacao]);
            }

            $pdo->commit();
            $success = "Cadastro realizado com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erro ao salvar os dados: " . $e->getMessage();
        }
    }
}
?>

<!-- Exibe erros ou sucesso -->
<?php if (!empty($errors)): ?>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($success): ?>
    <p><?php echo htmlspecialchars($success); ?></p>
    <script>
        // Redireciona para index.php após 3 segundos
        setTimeout(function () {
            window.location.href = '../index.php';
        }, 3000); // 3000 milissegundos = 3 segundos
    </script>
<?php endif; ?>

<!-- Página de cadastro com estilo do formulário -->
<section class="registration-form">
    <div class="container">
        <h2>Cadastro de Usuário</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="usuario_nome">Nome Completo:</label>
                <input type="text" id="usuario_nome" name="usuario_nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="usuario_email">E-mail:</label>
                <input type="email" id="usuario_email" name="usuario_email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirma_senha">Confirme a Senha:</label>
                <input type="password" id="confirma_senha" name="confirma_senha" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="usuario_cep">CEP:</label>
                <input type="text" id="usuario_cep" name="usuario_cep" class="form-control">
            </div>
            <div class="form-group">
                <label for="usuario_endereco">Endereço:</label>
                <input type="text" id="usuario_endereco" name="usuario_endereco" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="usuario_endereco_num">Número:</label>
                <input type="text" id="usuario_endereco_num" name="usuario_endereco_num" class="form-control">
            </div>
            <div class="form-group">
                <label for="usuario_endereco_complemento">Complemento:</label>
                <input type="text" id="usuario_endereco_complemento" name="usuario_endereco_complemento" class="form-control">
            </div>
            <div class="form-group">
                <label for="usuario_bairro">Bairro:</label>
                <input type="text" id="usuario_bairro" name="usuario_bairro" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="usuario_cidade">Cidade:</label>
                <input type="text" id="usuario_cidade" name="usuario_cidade" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="usuario_estado">Estado (UF):</label>
                <input type="text" id="usuario_estado" name="usuario_estado" class="form-control" maxlength="2" readonly>
            </div>
            <div class="form-group">
                <label for="usuario_documento">Documento (CPF ou CNPJ):</label>
                <input type="text" id="usuario_documento" name="usuario_documento" class="form-control" maxlength="18">
            </div>
            <div class="form-group">
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" class="form-control" maxlength="15">
            </div>
            <div class="form-group">
                <label for="eh_empresa">É uma organização?</label>
                <select id="eh_empresa" name="eh_empresa" class="form-control">
                    <option value="0">Não</option>
                    <option value="1">Sim</option>
                </select>
            </div>
            <div class="form-group" id="organizacao_fields" style="display: none;">
                <label for="tipo_organizacao">Tipo de Organização:</label>
                <input type="text" id="tipo_organizacao" name="tipo_organizacao" class="form-control">
                <label for="area_atuacao">Área de Atuação:</label>
                <input type="text" id="area_atuacao" name="area_atuacao" class="form-control">
            </div>
            <div class="form-group">
                <label for="tipos">O que deseja?</label>
                <select id="tipos" name="tipos[]" class="form-control" multiple>
                    <option value="doar">Doar</option>
                    <option value="receber">Receber</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
    </div>
</section>

<script>
    document.getElementById("eh_empresa").addEventListener("change", function() {
        const organizacaoFields = document.getElementById("organizacao_fields");
        if (this.value == "1") {
            organizacaoFields.style.display = "block";
        } else {
            organizacaoFields.style.display = "none";
        }
    });
</script>

