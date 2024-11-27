<?php
require_once '../includes/verifica_gestor.php';
// Função para pegar as tintas disponíveis
function getTintasDisponiveis()
{
    global $pdo;
    $stmt = $pdo->query("SELECT id_tintas, nome_tintas, quantidade_tintas_disponivel, data_validade_tintas 
                         FROM Tintas WHERE excluido = 0 AND quantidade_tintas_disponivel > 0");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$tintas = getTintasDisponiveis();
$errors = []; // Array para armazenar mensagens de erro

// Processar o formulário de mistura de tintas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tinta1 = $_POST['id_tinta1'];
    $quantidade_tinta1 = $_POST['quantidade_tinta1'];
    $id_tinta2 = $_POST['id_tinta2'];
    $quantidade_tinta2 = $_POST['quantidade_tinta2'];
    $nome_novo_tinta = $_POST['nome_novo_tinta'];
    $codigo_RGB = $_POST['codigo_RGB'] ?? null;

    if (empty($id_tinta1) || empty($id_tinta2) || empty($nome_novo_tinta)) {
        $errors[] = "Por favor, selecione duas tintas e forneça o nome para a nova mistura.";
    }

    if ($id_tinta1 === $id_tinta2) {
        $errors[] = "Não é possível selecionar a mesma tinta para a mistura.";
    }

    if (empty($errors)) {
        try {
            // Chama a stored procedure para mesclar as tintas
            $stmt = $pdo->prepare("CALL mesclar_tintas(:id_tinta1, :id_tinta2, :quantidade_tinta1, :quantidade_tinta2, :nome_novo_tinta, @nova_tinta_id)");
            $stmt->execute([
                ':id_tinta1' => $id_tinta1,
                ':id_tinta2' => $id_tinta2,
                ':quantidade_tinta1' => $quantidade_tinta1,
                ':quantidade_tinta2' => $quantidade_tinta2,
                ':nome_novo_tinta' => $nome_novo_tinta
            ]);

            // Recuperar o ID da nova tinta
            $stmt = $pdo->query("SELECT @nova_tinta_id AS nova_tinta_id");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $nova_tinta_id = $result['nova_tinta_id'];

            // Atualizar cor RGB da nova tinta (se fornecida)
            if ($codigo_RGB) {
                $stmt = $pdo->prepare("UPDATE Tintas SET codigo_RGB = :codigo_RGB WHERE id_tintas = :id_tinta");
                $stmt->execute([
                    ':codigo_RGB' => $codigo_RGB,
                    ':id_tinta' => $nova_tinta_id
                ]);
            }

            echo "<script>alert('Mistura realizada com sucesso! Nova tinta criada com ID: {$nova_tinta_id}');</script>";
        } catch (Exception $e) {
            $errors[] = "Erro ao realizar a mistura: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Mixar Tintas - Banco de Tintas</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <h1>Mistura de Tintas</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="mistura_tintas.php">
            <div class="form-group">
                <label for="id_tinta1">Selecione a Primeira Tinta:</label>
                <select name="id_tinta1" id="id_tinta1" required>
                    <option value="">Selecione</option>
                    <?php foreach ($tintas as $tinta): ?>
                        <option value="<?php echo $tinta['id_tintas']; ?>" <?php echo (isset($_POST['id_tinta1']) && $_POST['id_tinta1'] == $tinta['id_tintas']) ? 'selected' : ''; ?>>
                            <?php echo "{$tinta['nome_tintas']} - {$tinta['quantidade_tintas_disponivel']}L"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantidade_tinta1">Quantidade da Primeira Tinta (L):</label>
                <input type="number" step="0.01" name="quantidade_tinta1" id="quantidade_tinta1"
                    value="<?php echo $_POST['quantidade_tinta1'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="id_tinta2">Selecione a Segunda Tinta:</label>
                <select name="id_tinta2" id="id_tinta2" required>
                    <option value="">Selecione</option>
                    <?php foreach ($tintas as $tinta): ?>
                        <option value="<?php echo $tinta['id_tintas']; ?>" <?php echo (isset($_POST['id_tinta2']) && $_POST['id_tinta2'] == $tinta['id_tintas']) ? 'selected' : ''; ?>>
                            <?php echo "{$tinta['nome_tintas']} - {$tinta['quantidade_tintas_disponivel']}L"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantidade_tinta2">Quantidade da Segunda Tinta (L):</label>
                <input type="number" step="0.01" name="quantidade_tinta2" id="quantidade_tinta2"
                    value="<?php echo $_POST['quantidade_tinta2'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="nome_novo_tinta">Nome da Nova Tinta:</label>
                <input type="text" id="nome_novo_tinta" name="nome_novo_tinta"
                    value="<?php echo $_POST['nome_novo_tinta'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="codigo_RGB">Selecione a Cor da Nova Tinta (opcional):</label>
                <input type="color" id="codigo_RGB" name="codigo_RGB"
                    value="<?php echo $_POST['codigo_RGB'] ?? '#000000'; ?>">
            </div>

            <button type="submit">Criar Mistura</button>
        </form>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            const tinta1 = document.getElementById('id_tinta1').value;
            const tinta2 = document.getElementById('id_tinta2').value;

            if (tinta1 === tinta2) {
                e.preventDefault(); // Impede o envio do formulário
                alert('Selecione tintas diferentes para a mistura.');
            }
        });
    </script>
</body>

</html>