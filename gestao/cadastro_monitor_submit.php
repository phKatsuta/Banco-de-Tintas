<?php
// Inclui o arquivo de conexão
include 'conexao.php';

// Recebe os dados do formulário
$ra = $_POST['ra'];
$nome = $_POST['nome'];
$sobrenome = $_POST['sobrenome'];
$curso = $_POST['curso'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$telefones = $_POST['telefone'];

// Cria o nome completo
$nome_completo = $nome . " " . $sobrenome;

// Hash da senha (use uma função de hash segura como password_hash)
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Insere os dados no banco
$sql = "INSERT INTO Monitor (registro, nome_monitor, curso, email, senha_hash, gestor)
        VALUES ('$ra', '$nome_completo', '$curso', '$email', '$senha_hash', 1)";

if ($conn->query($sql) === TRUE) {
    $id_monitor = $conn->insert_id;

    // Insere os telefones
    foreach ($telefones as $telefone) {
        $sql = "INSERT INTO Monitor_telefone (id_monitor, telefone)
                VALUES ($id_monitor, '$telefone')";
        $conn->query($sql);
    }

    echo "Cadastro realizado com sucesso!";
} else {
    echo "Erro ao cadastrar: " . $conn->error;
}

$conn->close();

header("Location: cadastro_monitor.php");