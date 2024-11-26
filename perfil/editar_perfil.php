<?php
require_once '../includes/config.php'; // Arquivo com a conexão ao banco
session_start();
include '../includes/gera_menu.php';
include '../includes/via_cep.php';
include '../includes/busca_cep.php';
include '../includes/function_buscarEnderecoViaCep.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Redireciona para a página de login se não estiver logado
    header("Location: login.php");
    exit();
}

// Recupera o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Consulta para recuperar os dados do usuário no banco de dados
$query = "SELECT usuario_nome, usuario_cep, usuario_endereco, usuario_endereco_num, usuario_endereco_complemento, 
                 usuario_bairro, usuario_cidade, usuario_estado, usuario_email, telefone, usuario_documento
          FROM Usuarios WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$usuario_id]);

// Verifica se o usuário existe e recupera as informações
if ($stmt->rowCount() > 0) {
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Preenche as variáveis de sessão com os dados do usuário
    $_SESSION['usuario_nome'] = $user_data['usuario_nome'];
    $_SESSION['usuario_cep'] = $user_data['usuario_cep'];
    $_SESSION['usuario_endereco'] = $user_data['usuario_endereco'];
    $_SESSION['usuario_endereco_num'] = $user_data['usuario_endereco_num'];
    $_SESSION['usuario_endereco_complemento'] = $user_data['usuario_endereco_complemento'];
    $_SESSION['usuario_bairro'] = $user_data['usuario_bairro'];
    $_SESSION['usuario_cidade'] = $user_data['usuario_cidade'];
    $_SESSION['usuario_estado'] = $user_data['usuario_estado'];
    $_SESSION['usuario_email'] = $user_data['usuario_email'];
    $_SESSION['usuario_telefone'] = $user_data['telefone'];
    $_SESSION['usuario_documento'] = $user_data['usuario_documento'];
} else {
    // Caso o usuário não seja encontrado
    echo "Usuário não encontrado.";
    exit();
}
?>

<form action="salvar_perfil.php" method="POST" id="form">
    <label for="usuario_nome">Nome:</label>
    <input type="text" name="usuario_nome" id="usuario_nome"
        value="<?= isset($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : '' ?>" readonly
        required><br>

    <label for="cep">CEP:</label>
    <input type="text" name="usuario_cep" id="cep"
        value="<?= isset($_SESSION['usuario_cep']) ? htmlspecialchars($_SESSION['usuario_cep']) : '' ?>" readonly>

    <button type="button" id="buscarCep" hidden>Buscar CEP</button>
    <div id="loading-indicator" style="display: none;">Carregando...</div><br>


    <label for="usuario_endereco">Endereço:</label>
    <input type="text" name="usuario_endereco" id="usuario_endereco"
        value="<?= isset($_SESSION['usuario_endereco']) ? htmlspecialchars($_SESSION['usuario_endereco']) : '' ?>"
        readonly><br>

    <label for="endereco_num">Número:</label>
    <input type="text" name="usuario_endereco_num" id="endereco_num"
        value="<?= isset($_SESSION['usuario_endereco_num']) ? htmlspecialchars($_SESSION['usuario_endereco_num']) : '' ?>"
        readonly><br>

    <label for="endereco_complemento">Complemento:</label>
    <input type="text" name="usuario_endereco_complemento" id="endereco_complemento"
        value="<?= isset($_SESSION['usuario_endereco_complemento']) ? htmlspecialchars($_SESSION['usuario_endereco_complemento']) : '' ?>"
        readonly><br>

    <label for="bairro">Bairro:</label>
    <input type="text" name="usuario_bairro" id="usuario_bairro"
        value="<?= isset($_SESSION['usuario_bairro']) ? htmlspecialchars($_SESSION['usuario_bairro']) : '' ?>"
        readonly><br>

    <label for="cidade">Cidade:</label>
    <input type="text" name="usuario_cidade" id="usuario_cidade"
        value="<?= isset($_SESSION['usuario_cidade']) ? htmlspecialchars($_SESSION['usuario_cidade']) : '' ?>"
        readonly><br>

    <label for="estado">Estado:</label>
    <input type="text" name="usuario_estado" id="usuario_estado"
        value="<?= isset($_SESSION['usuario_estado']) ? htmlspecialchars($_SESSION['usuario_estado']) : '' ?>"
        readonly><br>

    <label for="usuario_email">Email:</label>
    <input type="email" name="usuario_email" id="usuario_email"
        value="<?= isset($_SESSION['usuario_email']) ? htmlspecialchars($_SESSION['usuario_email']) : '' ?>" readonly
        required><br>

    <label for="telefone">Telefone:</label>
    <input type="text" name="telefone" id="telefone"
        value="<?= isset($_SESSION['usuario_telefone']) ? htmlspecialchars($_SESSION['usuario_telefone']) : '' ?>"
        readonly required><br>

    <label for="usuario_documento">Documento (CPF ou CNPJ):</label>
    <input type="text" name="usuario_documento" id="usuario_documento"
        value="<?= isset($_SESSION['usuario_documento']) ? htmlspecialchars($_SESSION['usuario_documento']) : '' ?>"
        readonly><br>

    <!-- Botão Editar -->
    <button type="button" onclick="habilitarEdicao()">Editar</button>

    <button type="submit" id="salvarBtn" style="display:none;">Salvar Alterações</button>
</form>

<script type="text/javascript" src="../SCRIPT/script_editar.js"></script>
</body>

</html>

<?php
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'sucesso') {
        echo "<p>Alterações salvas com sucesso!</p>";
    } elseif ($_GET['msg'] == 'nenhuma_alteracao') {
        echo "<p>Nenhuma alteração foi feita.</p>";
    }
}
?>