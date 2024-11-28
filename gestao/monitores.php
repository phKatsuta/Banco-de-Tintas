<?php
require_once '../includes/verifica_gestor.php';
require_once __DIR__ . '/config.php';
// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}
include '../templates/header.php';

$tabela = mysql_query($pdo, "SELECT
	*
FROM
	usuarios u 
INNER JOIN
	usuario_tipos t 
ON
	u.id = t.usuario_id
WHERE

	t.tipo = 'Monitor';")
?>
<html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitores</title>
</head>
<body>
<div align="center">
        <h1>MONITORES</h1>
        <table border="1">
            <tr>
            <th>ID</th>
                <th>Nome</th>
                <th>CEP</th>
                <th>ENDEREÇO</th>
                <th>NÚMERO</th>
                <th>COMPLEMENTO</th>
                <th>BAIRRO</th>
                <th>CIDADE</th>
                <th>ESTADO</th>
                <th>EMAIL</th>
                <th>É EMPRESA?</th>
                <th>DOCUMENTO</th>
                <th>TELEFONE</th>
            </tr>
            <?php
                while($linha = mysqli_fetch_array($tabela))
                {

            ?>
                <tr>
                    <td><?php echo $linha["id"]?></td>
                    <td><?php echo $linha["usuario_nome"]?></td>
                    <td><?php echo $linha["usuario_cep"]?></td>
                    <td><?php echo $linha["usuario_endereco"]?></td>
                    <td><?php echo $linha["usuario_endereco_num"]?></td>
                    <td><?php echo $linha["usuario_endereco_complemento"]?></td>
                    <td><?php echo $linha["usuario_bairro"]?></td>
                    <td><?php echo $linha["usuario_cidade"]?></td>
                    <td><?php echo $linha["usuario_estado"]?></td>
                    <td><?php echo $linha["eh_empresa"]?></td>
                    <td><?php echo $linha["usuario_documento"]?></td>
                    <td><?php echo $linha["telefone"]?></td>

                    <td align="center">
                        <a href="">
                            <img src="imagens/excluir.png" alt="Excluir">
                        </a>
                    </td>
                </tr>
            <?php 
                }
            ?>
        </table>
    </div>
</body>
</html>