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
<?php include '../templates/header.php'; ?>
<main class="container">
    <!-- Modal de Login -->
    <div id="loginModal" class="modal" aria-hidden="true" role="dialog">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h2>Acessar o Sistema</h2>
            </div>
            <form method="POST" action="../login.php" class="modal-form">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" placeholder="Digite seu email" required>
                <label for="password">Senha:</label>
                <input type="password" name="password" id="password" placeholder="Digite sua senha" required>
                <div class="checkbox-group">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Lembrar-me</label>
                </div>
                <button type="submit" class="btn">Entrar</button>
            </form>
        </div>
    </div>
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

    <h1>Cadastro - Banco de Tintas</h1>
    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (!empty($success)): ?>
        <div style="color: green;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="form">
        <label for="usuario_nome">Nome:</label>
        <input type="text" name="usuario_nome" id="usuario_nome" required><br>

        <!--- Endereço --->
        <label for="cep">CEP:</label>
        <input type="text" name="usuario_cep" id="cep" oninput="aplicarMascaraCEP(this)" placeholder="Digite o CEP">

        <button type="button" id="buscarCep">Buscar CEP</button>
        <div id="loading-indicator" style="display: none;">Carregando...</div><br>

        <label for="usuario_endereco">Endereço:</label>
        <input type="text" name="usuario_endereco" id="usuario_endereco" readonly>

        <label for="endereco_num">Número:</label>
        <input type="text" name="usuario_endereco_num" id="endereco_num"><br>

        <label for="endereco_complemento">Complemento:</label>
        <input type="text" name="usuario_endereco_complemento" id="endereco_complemento"><br>

        <label for="bairro">Bairro:</label>
        <input type="text" id="usuario_bairro" readonly><br>

        <label for="cidade">Cidade:</label>
        <input type="text" id="usuario_cidade" readonly><br>

        <label for="estado">Estado:</label>
        <input type="text" id="usuario_estado" readonly><br>

        <!--- Login --->
        <label for="usuario_email">E-mail:</label>
        <input type="email" name="usuario_email" id="usuario_email" required><br>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" id="senha" required><br>

        <label for="confirma_senha">Confirme a Senha:</label>
        <input type="password" name="confirma_senha" id="confirma_senha" required><br>

        <label for="usuario_documento">Documento (CPF ou CNPJ):</label>
        <input type="text" name="usuario_documento" id="usuario_documento" oninput="mascaraDocumento(this)"
            maxlength="18" placeholder="Digite CPF ou CNPJ">
        <span id=" usuario_documento_Error" style="color: red; display: none;">CPF ou CNPJ é obrigatório para
            organizações.</span><br>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" maxlength="15" placeholder="(XX) XXXXX-XXXX"><br>

        <!--- Caso seja uma organização --->
        <label for="eh_empresa">É uma organização?</label>
        <input type="checkbox" name="eh_empresa" id="eh_empresa" value="1" onchange="toggleOrganizacao(this)"><br>

        <div id="organizacao_fields" style="display: none;">
            <label for="tipo_organizacao">Tipo de Organização:</label>
            <input type="text" name="tipo_organizacao" id="tipo_organizacao"><br>

            <label for="area_atuacao">Área de Atuação:</label>
            <input type="text" name="area_atuacao" id="area_atuacao"><br>
        </div>

        <!--- Tipo de usuario --->
        <label>Deseja:</label><br>
        <input type="checkbox" name="tipos[]" value="Doador" id="doador">
        <label for="doador">Doar tintas</label><br>
        <input type="checkbox" name="tipos[]" value="Beneficiario" id="beneficiario">
        <label for="beneficiario">Receber tintas</label><br>

        <button type="submit">Cadastrar</button>
    </form>
</main>
<script type="text/javascript" src="../SCRIPT/script_cadastro.js"></script>
<script type="text/javascript" src="../SCRIPT/script.js"></script>
<?php include '../templates/footer.php'; ?>