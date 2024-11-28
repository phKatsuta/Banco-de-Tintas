<?php
require_once __DIR__ . '/config.php';
session_start();

// Função para obter os tipos de usuário diretamente do banco
if (!function_exists('getUserTypes')) {
    function getUserTypes($pdo, $usuario_id)
    {
        $sql = "SELECT tipo FROM Usuario_Tipos WHERE usuario_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);

        $tipos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tipos[] = $row['tipo'];
        }
        return $tipos;
    }
}

function getUserById($pdo, $userId) {
    try {
        $sql = "SELECT * FROM Usuarios WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['usuario_nome']; // Retorna o nome correto
        } else {
            return null; // Caso o usuário não seja encontrado
        }
    } catch (PDOException $e) {
        echo "Erro ao buscar usuário: " . $e->getMessage();
        return null;
    }
}

// Função para gerar o menu principal com base nos tipos de usuário
if (!function_exists('gerarMenuPrincipal')) {
    function gerarMenuPrincipal($tipos_usuario, $usuario_nome = 'Usuário')
    {
        echo '<nav class="user-menu">';
        echo '<h2>Bem-vindo, ' . htmlspecialchars($usuario_nome) . '!</h2>';
        echo '<ul>';
        echo '<li><a href="' . BASE_URL . 'index.php">Início</a></li>';

        // Menu para Doador
        if (in_array('Doador', $tipos_usuario)) {
            echo '<li><a href="' . BASE_URL . 'doacao/minhas_doacoes.php">Minhas Doações</a></li>';
            echo '<li><a href="' . BASE_URL . 'doacao/doacao.php">Cadastrar Doações</a></li>';
        }

        // Menu para Beneficiário
        if (in_array('Beneficiario', $tipos_usuario)) {
            echo '<li><a href="' . BASE_URL . 'solicitacao/minhas_solicitacoes.php">Minhas Solicitações</a></li>';
            echo '<li><a href="' . BASE_URL . 'solicitacao/solicitacao.php">Solicitar Tinta</a></li>';
        }

        echo '<li><a href="' . BASE_URL . 'perfil.php">Meu Perfil</a></li>';
        echo '<li><a href="' . BASE_URL . 'logout.php" class="logout-btn">Sair</a></li>';
        echo '</ul>';
        echo '</nav>';
    }
}

$tipos_usuario = getUserTypes($pdo, $_SESSION['user_id']);
$usuario_nome = getUserById($pdo, $_SESSION['user_id']);
gerarMenuPrincipal($tipos_usuario, $usuario_nome);
?>
