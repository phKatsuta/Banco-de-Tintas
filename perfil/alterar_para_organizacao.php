<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $cnpj = $_POST['usuario_documento'];

    // Valida CNPJ simples
    if (strlen(preg_replace('/\D/', '', $cnpj)) === 14) {
        $sql = "UPDATE Usuarios SET eh_empresa = 1, usuario_documento = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cnpj, $usuario_id]);

        header("Location: perfil.php");
        exit;
    } else {
        echo "CNPJ inválido!";
    }
}
?>

<h3>Alterar para Organização</h3>
<form method="POST">
    <label for="usuario_documento">CNPJ:</label>
    <input type="text" name="usuario_documento" id="usuario_documento" required>
    <button type="submit">Alterar para Organização</button>
</form>
