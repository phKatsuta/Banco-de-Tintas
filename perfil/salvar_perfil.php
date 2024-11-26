<?php
require_once '../includes/config.php'; // Arquivo com a conexão ao banco
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Redireciona para a página de login se não estiver logado
    header("Location: login.php");
    exit();
}

// Recupera o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize e valida os dados recebidos do formulário
    $usuario_nome = isset($_POST['usuario_nome']) ? htmlspecialchars($_POST['usuario_nome']) : '';
    $usuario_cep = isset($_POST['usuario_cep']) ? htmlspecialchars($_POST['usuario_cep']) : '';
    $usuario_endereco = isset($_POST['usuario_endereco']) ? htmlspecialchars($_POST['usuario_endereco']) : '';
    $usuario_endereco_num = isset($_POST['usuario_endereco_num']) ? htmlspecialchars($_POST['usuario_endereco_num']) : '';
    $usuario_endereco_complemento = isset($_POST['usuario_endereco_complemento']) ? htmlspecialchars($_POST['usuario_endereco_complemento']) : '';
    $usuario_bairro = isset($_POST['usuario_bairro']) ? htmlspecialchars($_POST['usuario_bairro']) : '';
    $usuario_cidade = isset($_POST['usuario_cidade']) ? htmlspecialchars($_POST['usuario_cidade']) : '';
    $usuario_estado = isset($_POST['usuario_estado']) ? htmlspecialchars($_POST['usuario_estado']) : '';
    $usuario_email = isset($_POST['usuario_email']) ? htmlspecialchars($_POST['usuario_email']) : '';
    $usuario_telefone = isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : '';
    $usuario_documento = isset($_POST['usuario_documento']) ? htmlspecialchars($_POST['usuario_documento']) : '';

    // Consulta SQL para atualizar os dados do usuário
    $query = "UPDATE Usuarios SET 
                usuario_nome = ?, 
                usuario_cep = ?, 
                usuario_endereco = ?, 
                usuario_endereco_num = ?, 
                usuario_endereco_complemento = ?, 
                usuario_bairro = ?, 
                usuario_cidade = ?, 
                usuario_estado = ?, 
                usuario_email = ?, 
                telefone = ?, 
                usuario_documento = ? 
              WHERE id = ?";

    // Prepara a consulta SQL
    $stmt = $pdo->prepare($query);

    // Executa a consulta, passando os dados do formulário e o ID do usuário
    $stmt->execute([
        $usuario_nome, 
        $usuario_cep, 
        $usuario_endereco, 
        $usuario_endereco_num, 
        $usuario_endereco_complemento, 
        $usuario_bairro, 
        $usuario_cidade, 
        $usuario_estado, 
        $usuario_email, 
        $usuario_telefone, 
        $usuario_documento, 
        $usuario_id
    ]);

    // Verifica se a atualização foi bem-sucedida
    if ($stmt->rowCount() > 0) {
        // Atualiza as variáveis de sessão com os novos valores
        $_SESSION['usuario_nome'] = $usuario_nome;
        $_SESSION['usuario_cep'] = $usuario_cep;
        $_SESSION['usuario_endereco'] = $usuario_endereco;
        $_SESSION['usuario_endereco_num'] = $usuario_endereco_num;
        $_SESSION['usuario_endereco_complemento'] = $usuario_endereco_complemento;
        $_SESSION['usuario_bairro'] = $usuario_bairro;
        $_SESSION['usuario_cidade'] = $usuario_cidade;
        $_SESSION['usuario_estado'] = $usuario_estado;
        $_SESSION['usuario_email'] = $usuario_email;
        $_SESSION['usuario_telefone'] = $usuario_telefone;
        $_SESSION['usuario_documento'] = $usuario_documento;

        // Redireciona de volta para o perfil com uma mensagem de sucesso
        header("Location: editar_perfil.php?msg=sucesso");
        exit();
    } else {
        // Se não houver alteração (por exemplo, se os dados forem os mesmos)
        header("Location: editar_perfil.php?msg=nenhuma_alteracao");
        exit();
    }
}
?>
