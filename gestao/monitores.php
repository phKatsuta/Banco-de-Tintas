<?php
require_once '../includes/verifica_gestor.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

include '../templates/header.php';

try {
    // Consulta segura com PDO
    $stmt = $pdo->prepare("
        SELECT 
            u.id, u.usuario_nome, u.usuario_cep, u.usuario_endereco, u.usuario_endereco_num, 
            u.usuario_endereco_complemento, u.usuario_bairro, u.usuario_cidade, u.usuario_estado,
            u.usuario_email, u.eh_empresa, u.usuario_documento, u.telefone
        FROM 
            usuarios u
        INNER JOIN 
            usuario_tipos t 
        ON 
            u.id = t.usuario_id
        WHERE 
            t.tipo = 'Monitor'
    ");
    $stmt->execute();
    $monitores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao buscar os monitores: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitores</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
<div align="center">
    <h1>Monitores</h1>
    <?php if (count($monitores) > 0): ?>
        <table class="tabela-monitores">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CEP</th>
                    <th>Endereço</th>
                    <th>Número</th>
                    <th>Complemento</th>
                    <th>Bairro</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Email</th>
                    <th>É Empresa?</th>
                    <th>Documento</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monitores as $monitor): ?>
                    <tr>
                        <td><?= htmlspecialchars($monitor["id"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_nome"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_cep"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_endereco"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_endereco_num"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_endereco_complemento"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_bairro"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_cidade"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_estado"]) ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_email"]) ?></td>
                        <td><?= $monitor["eh_empresa"] ? 'Sim' : 'Não' ?></td>
                        <td><?= htmlspecialchars($monitor["usuario_documento"]) ?></td>
                        <td><?= htmlspecialchars($monitor["telefone"]) ?></td>
                        <td>
                            <a href="excluir_monitor.php?id=<?= $monitor['id'] ?>" onclick="return confirm('Deseja realmente excluir este monitor?')">
                                <img src="../imagens/excluir.png" alt="Excluir" title="Excluir">
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Não há monitores cadastrados no sistema.</p>
    <?php endif; ?>
</div>
<script src="../SCRIPT/script.js"></script>
<?php include '../templates/footer.php'; ?>
</body>
</html>
