<?php
// Função para validar e processar o formulário de doação
function processDonationForm($pdo, $form_data, $usuario_id, $tipos_usuario)
{
    $errors = [];
    $local_doado = trim($form_data['local_doado']);
    $tintas = $form_data['tintas'] ?? [];
    $id_doador = in_array('Doador', $tipos_usuario) ? $usuario_id : intval($form_data['id_doador']);

    // Validações
    if (empty($local_doado)) {
        $errors[] = "O local de doação é obrigatório.";
    }
    if (empty($tintas)) {
        $errors[] = "Você deve adicionar ao menos uma tinta.";
    }

    // Processamento
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Inserir a doação
            $stmt = $pdo->prepare("INSERT INTO Doacao (id_doador, data_doacao, local_doado) VALUES (?, CURRENT_TIMESTAMP, ?)");
            $stmt->execute([$id_doador, $local_doado]);
            $id_doacao = $pdo->lastInsertId();

            // Inserir tintas e associá-las
            foreach ($tintas as $tinta) {
                $stmt_tinta = $pdo->prepare("INSERT INTO Tintas (nome_tintas, marca, linha, acabamento, quantidade_tintas_disponivel, data_validade_tintas) 
                                             VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_tinta->execute([
                    $tinta['nome_tintas'],
                    $tinta['marca'],
                    $tinta['linha'],
                    $tinta['acabamento'],
                    $tinta['quantidade'],
                    $tinta['data_validade']
                ]);
                $id_tinta = $pdo->lastInsertId();

                $stmt_doacao_tinta = $pdo->prepare("INSERT INTO Doacao_tintas (id_doacao, id_tintas, quantidade_tintas_doada) VALUES (?, ?, ?)");
                $stmt_doacao_tinta->execute([$id_doacao, $id_tinta, $tinta['quantidade']]);
            }

            $pdo->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erro ao salvar a doação: " . $e->getMessage();
        }
    }

    return ["success" => false, "errors" => $errors];
}
