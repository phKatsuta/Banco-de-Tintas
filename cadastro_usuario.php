<?php
// Configurações de conexão com o banco de dados
$servername = "localhost";  // Alterar para o seu servidor de banco de dados
$username = "root";         // Usuário do banco de dados
$password = "";             // Senha do banco de dados
$dbname = "seu_banco_de_dados";  // Nome do banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanear e validar os dados recebidos
    $usuario_nome = trim($_POST['usuario_nome']);
    $usuario_email = trim($_POST['usuario_email']);
    $senha = trim($_POST['senha']);
    $usuario_cep = trim($_POST['usuario_cep']);
    $usuario_endereco = trim($_POST['usuario_endereco']);
    $usuario_endereco_num = trim($_POST['usuario_endereco_num']);
    $usuario_endereco_complemento = trim($_POST['usuario_endereco_complemento']);
    $usuario_bairro = trim($_POST['usuario_bairro']);
    $usuario_cidade = trim($_POST['usuario_cidade']);
    $usuario_estado = trim($_POST['usuario_estado']);
    $usuario_documento = trim($_POST['usuario_documento']);
    $telefone = trim($_POST['telefone']);
    $eh_empresa = (int) $_POST['eh_empresa']; // Converte para inteiro

    // Validar e-mail
    if (!filter_var($usuario_email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('E-mail inválido!'); window.location.href='cadastro_usuario.php';</script>";
        exit;
    }

    // Validar se o email já existe
    $check_email = $conn->prepare("SELECT id FROM Usuarios WHERE usuario_email = ?");
    $check_email->bind_param("s", $usuario_email);
    $check_email->execute();
    $check_email_result = $check_email->get_result();
    
    if ($check_email_result->num_rows > 0) {
        echo "<script>alert('E-mail já cadastrado!'); window.location.href='cadastro_usuario.php';</script>";
        exit;
    }

    // Gerar hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Preparar a consulta SQL com prepared statements
    $stmt = $conn->prepare("INSERT INTO Usuarios (usuario_nome, usuario_email, senha_hash, usuario_cep, usuario_endereco, usuario_endereco_num, usuario_endereco_complemento, usuario_bairro, usuario_cidade, usuario_estado, usuario_documento, telefone, eh_empresa) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        // Bind os parâmetros para evitar SQL Injection
        $stmt->bind_param("ssssssssssssi", $usuario_nome, $usuario_email, $senha_hash, $usuario_cep, $usuario_endereco, 
        $usuario_endereco_num, $usuario_endereco_complemento, $usuario_bairro, $usuario_cidade, $usuario_estado, $usuario_documento, $telefone, $eh_empresa);
        
        // Executar a consulta
        if ($stmt->execute()) {
            // Redirecionar para a página de login após sucesso
            echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='single-blog.html';</script>";
            exit;
        } else {
            // Caso haja erro na execução
            echo "<script>alert('Erro ao cadastrar usuário!'); window.location.href='cadastro_usuario.php';</script>";
            exit;
        }

        // Fechar a declaração preparada
        $stmt->close();
    } else {
        echo "<script>alert('Erro na preparação da consulta!'); window.location.href='cadastro_usuario.php';</script>";
        exit;
    }
}

// Fechar a conexão
$conn->close();
?>
