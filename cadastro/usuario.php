<?php
require_once '../includes/gera_menu.php';
include '../includes/via_cep.php';
include '../includes/busca_cep.php';
include '../includes/function_buscarEnderecoViaCep.php';

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["usuario_nome"]);
    $cep = trim($_POST["usuario_cep"]);
    $endereco = trim($_POST["usuario_endereco"] ?? "");
    $endereco_num = trim($_POST["usuario_endereco_num"]);
    $endereco_complemento = trim($_POST["usuario_endereco_complemento"]);
    $bairro = trim($_POST["usuario_bairro"] ?? "");
    $cidade = trim($_POST["usuario_cidade"] ?? "");
    $estado = trim($_POST["usuario_estado"] ?? "");
    $email = trim($_POST["usuario_email"]);
    $senha = $_POST["senha"];
    $confirma_senha = $_POST["confirma_senha"];
    $documento = trim($_POST["usuario_documento"]);
    $telefone = trim($_POST["telefone"]);
    $eh_empresa = isset($_POST["eh_empresa"]) ? 1 : 0;
    $tipo_organizacao = $eh_empresa ? trim($_POST["tipo_organizacao"]) : null;
    $area_atuacao = $eh_empresa ? trim($_POST["area_atuacao"]) : null;
    $tipos = isset($_POST["tipos"]) ? $_POST["tipos"] : [];

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

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $sql_usuario = "INSERT INTO Usuarios (
                usuario_nome, usuario_cep, usuario_endereco, usuario_endereco_num, usuario_endereco_complemento, usuario_bairro, usuario_cidade, 
                usuario_estado, usuario_email, senha_hash, usuario_documento, telefone, eh_empresa
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql_usuario);
            $stmt->execute([$nome, $cep, $endereco, $endereco_num, $endereco_complemento, $bairro, $cidade, $estado, $email, $senha_hash, $documento, $telefone, $eh_empresa]);

            $usuario_id = $pdo->lastInsertId();

            $sql_tipo = "INSERT INTO Usuario_Tipos (usuario_id, tipo) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql_tipo);
            foreach ($tipos as $tipo) {
                $stmt->execute([$usuario_id, $tipo]);
            }

            if ($eh_empresa) {
                $sql_organizacao = "INSERT INTO Organizacao (id_organizacao, tipo_organizacao, area_atuacao) VALUES (?, ?, ?)";
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

<?php if (!empty($errors)): ?>
    <ul style="color: red; font-family: Arial, sans-serif; list-style-type: none; padding: 0;">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color: green; font-family: Arial, sans-serif;"><?= htmlspecialchars($success) ?></p>
    <script>
        setTimeout(function () {
            window.location.href = '../index.php';
        }, 3000);
    </script>
<?php endif; ?>

<?php include '../templates/header.php' ?>

<h1 style="font-family: Arial, sans-serif; text-align: center;">Cadastro - Banco de Tintas</h1>

<form method="POST" action="" id="form" style="width: 100%; max-width: 600px; margin: auto; font-family: Arial, sans-serif;">
    <label for="usuario_nome" style="display: block; margin-bottom: 5px;">Nome:</label>
    <input type="text" name="usuario_nome" id="usuario_nome" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="cep" style="display: block; margin-bottom: 5px;">CEP:</label>
    <input type="text" name="usuario_cep" id="cep" oninput="aplicarMascaraCEP(this)" placeholder="Digite o CEP" style="width: calc(70% - 10px); padding: 8px; margin-bottom: 10px;">
    <button type="button" id="buscarCep" style="width: 28%; padding: 8px; margin-left: 2%;">Buscar CEP</button>
    <div id="loading-indicator" style="display: none; margin-top: 5px;">Carregando...</div>

    <label for="usuario_endereco" style="display: block; margin-top: 10px;">Endereço:</label>
    <input type="text" name="usuario_endereco" id="usuario_endereco" readonly style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="endereco_num" style="display: block;">Número:</label>
    <input type="text" name="usuario_endereco_num" id="endereco_num" style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="endereco_complemento" style="display: block;">Complemento:</label>
    <input type="text" name="usuario_endereco_complemento" id="endereco_complemento" style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="usuario_bairro" style="display: block;">Bairro:</label>
    <input type="text" id="usuario_bairro" readonly style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="usuario_cidade" style="display: block;">Cidade:</label>
    <input type="text" id="usuario_cidade" readonly style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="usuario_estado" style="display: block;">Estado:</label>
    <input type="text" id="usuario_estado" readonly style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="usuario_email" style="display: block;">E-mail:</label>
    <input type="email" name="usuario_email" id="usuario_email" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="senha" style="display: block;">Senha:</label>
    <input type="password" name="senha" id="senha" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="confirma_senha" style="display: block;">Confirme a Senha:</label>
    <input type="password" name="confirma_senha" id="confirma_senha" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="usuario_documento" style="display: block;">Documento (CPF ou CNPJ):</label>
    <input type="text" name="usuario_documento" id="usuario_documento" oninput="mascaraDocumento(this)" maxlength="18" placeholder="Digite CPF ou CNPJ" style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="telefone" style="display: block;">Telefone:</label>
    <input type="text" id="telefone" name="telefone" maxlength="15" placeholder="(XX) XXXXX-XXXX" style="width: 100%; padding: 8px; margin-bottom: 10px;">

    <label for="eh_empresa" style="display: block;">É uma organização?</label>
    <input type="checkbox" name="eh_empresa" id="eh_empresa" value="1" onchange="toggleOrganizacao(this)" style="margin-bottom: 10px;">

    <div id="organizacao_fields" style="display: none;">
        <label for="tipo_organizacao" style="display: block;">Tipo de Organização:</label>
        <input type="text" name="tipo_organizacao" id="tipo_organizacao" style="width: 100%; padding: 8px; margin-bottom: 10px;">

        <label for="area_atuacao" style="display: block;">Área de Atuação:</label>
        <input type="text" name="area_atuacao" id="area_atuacao" style="width: 100%; padding: 8px; margin-bottom: 10px;">
    </div>

    <fieldset style="margin-top: 15px; border: 1px solid #ddd; padding: 10px;">
        <legend>Tipos de Cadastro</legend>
        <label>
            <input type="checkbox" name="tipos[]" value="doar" style="margin-right: 5px;"> Doar
        </label>
        <label style="margin-left: 15px;">
            <input type="checkbox" name="tipos[]" value="receber" style="margin-right: 5px;"> Receber
        </label>
    </fieldset>

    <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-top: 15px;">Cadastrar</button>
</form>

<script src="../SCRIPT/script_cadastro.js"></script>
<script src="../SCRIPT/script.js"></script>
<?php include '../templates/footer.php'; ?>
