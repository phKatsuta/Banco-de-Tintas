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
    $usuario_nome = $_POST['usuario_nome'];
    $usuario_email = $_POST['usuario_email'];
    $senha = $_POST['senha'];
    $usuario_cep = $_POST['usuario_cep'];
    $usuario_endereco = $_POST['usuario_endereco'];
    $usuario_endereco_num = $_POST['usuario_endereco_num'];
    $usuario_endereco_complemento = $_POST['usuario_endereco_complemento'];
    $usuario_bairro = $_POST['usuario_bairro'];
    $usuario_cidade = $_POST['usuario_cidade'];
    $usuario_estado = $_POST['usuario_estado'];
    $usuario_documento = $_POST['usuario_documento'];
    $telefone = $_POST['telefone'];
    $eh_empresa = $_POST['eh_empresa'];

    // Gerar hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // SQL para inserir dados no banco de dados
    $sql = "INSERT INTO usuarios (usuario_nome, usuario_email, senha, usuario_cep, usuario_endereco, usuario_endereco_num, usuario_endereco_complemento, usuario_bairro, usuario_cidade, usuario_estado, usuario_documento, telefone, eh_empresa)
    VALUES ('$usuario_nome', '$usuario_email', '$senha_hash', '$usuario_cep', '$usuario_endereco', '$usuario_endereco_num', '$usuario_endereco_complemento', '$usuario_bairro', '$usuario_cidade', '$usuario_estado', '$usuario_documento', '$telefone', '$eh_empresa')";

if ($conn->query($sql) === TRUE) {
echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='login.html';</script>";
} else {
echo "Erro: " . $sql . "<br>" . $conn->error;
}
}

// Fechar a conexão
$conn->close();
?>
