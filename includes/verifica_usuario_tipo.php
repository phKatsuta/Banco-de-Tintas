<?php

// Função para verificar o tipo de usuário logado
function getUserTypes($pdo, $usuario_id) {
    $sql = "SELECT tipo FROM Usuario_Tipos WHERE usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);

    $tipos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipos[] = $row['tipo'];
    }
    return $tipos;
}
