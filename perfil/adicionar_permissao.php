<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $tipo = $_POST['permissionType'];

    // Verifica se já existe a permissão
    $sqlCheck = "SELECT COUNT(*) FROM Usuario_Tipos WHERE usuario_id = ? AND tipo = ?";
    $stmt = $pdo->prepare($sqlCheck);
    $stmt->execute([$usuario_id, $tipo]);

    if ($stmt->fetchColumn() == 0) {
        $sql = "INSERT INTO Usuario_Tipos (usuario_id, tipo) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $tipo]);

        header("Location: perfil.php"); // Redireciona para o perfil
        exit;
    }
}
?>

<h3>Adicionar Permissão</h3>
<form method="POST">
    <label for="permissionType">Escolha a permissão:</label>
    <select name="permissionType" id="permissionType">
        <option value="Beneficiario">Adicionar como Beneficiário</option>
        <option value="Doador">Adicionar como Doador</option>
    </select>
    <button type="submit">Adicionar Permissão</button>
</form>
