<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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
        echo '<h2>Bem vindo, ' . htmlspecialchars($usuario_nome) . '!</h2>';
        echo '<ul>';
        echo '<li><a href="' . BASE_URL . 'index.php">Início</a></li>';

        // Menu para Doador
        if (in_array('Doador', $tipos_usuario)) {
            echo '<p>Doador</p>';
            echo '<li><a href="' . BASE_URL . 'doacao/minhas_doacoes.php">Minhas Doações</a></li>';
            echo '<li><a href="' . BASE_URL . 'doacao/doacao.php">Cadastrar Doações</a></li>';
        }

        // Menu para Beneficiário
        if (in_array('Beneficiario', $tipos_usuario)) {
            echo '<p>Beneficiário</p>';
            echo '<li><a href="' . BASE_URL . 'solicitacao/minhas_solicitacoes.php">Minhas Solicitações</a></li>';
            echo '<li><a href="' . BASE_URL . 'solicitacao/solicitacao.php">Solicitar Tintas</a></li>';
        }

        // Menu para Gestor
        if (in_array('Gestor', $tipos_usuario)) {
            echo '<p>Gestor</p>';
            echo '<li><a href="' . BASE_URL . 'gestao/cadastrar_monitor.php">Cadastrar Monitor</a></li>';
            echo '<li><a href="' . BASE_URL . 'gestao/recebimento.php">Confirmar Doações</a></li>';
            echo '<li><a href="' . BASE_URL . 'gestao/analisar_solicitacao.php">Analisar Solicitações</a></li>';
            echo '<li><a href="' . BASE_URL . 'gestao/mistura_tintas.php">Misturar Tintas</a></li>';
        }

        // Menu para Monitor
        if (in_array('Monitor', $tipos_usuario)) {
            echo '<p>Monitor</p>';
            echo '<li><a href="' . BASE_URL . 'gestao/recebimento.php">Analisar Doações</a></li>';
            echo '<li><a href="' . BASE_URL . 'gestao/monitores.php">Monitoramento</a></li>';
        }

        echo '</ul>';
        echo '<form method="POST" action="' . BASE_URL . 'logout.php">';
        echo '<button type="submit" class="btn">Sair</button>';
        echo '</form>';
        echo '</nav>';
    }
}

// Função para gerar o menu de perfil
if (!function_exists('gerarMenuPerfil')) {
    function gerarMenuPerfil()
    {
        echo '<nav class="perfil-menu">';
        echo '<h3>Menu de Perfil</h3>';
        echo '<ul>';
        echo '<li><a href="' . BASE_URL . 'perfil/editar_perfil.php">Editar Perfil</a></li>';
        echo '<li><a href="' . BASE_URL . 'perfil/alterar_senha.php">Alterar senha</a></li>';
        echo '<li><a href="' . BASE_URL . 'perfil/excluir_conta.php">Excluir Conta</a></li>';
        echo '</ul>';
        echo '</nav>';
    }
}

// Controle de interface para o usuário logado
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $tipos_usuario = getUserTypes($pdo, $usuario_id); // Verificação dos tipos diretamente no banco
    $usuario_nome = getUserById($pdo, $usuario_id) ?? 'Usuário';

    // Exibe o menu principal
    gerarMenuPrincipal($tipos_usuario, $usuario_nome);

    // Exibe o menu de perfil
    gerarMenuPerfil();

    // Verifica a ação solicitada
    if (isset($_GET['acao'])) {
        if ($_GET['acao'] == 'editar') {
            include 'editar_perfil.php';
        } elseif ($_GET['acao'] == 'adicionar_permissao') {
            include 'adicionar_permissao.php';
        } elseif ($_GET['acao'] == 'alterar_para_organizacao') {
            include 'alterar_para_organizacao.php';
        } elseif ($_GET['acao'] == 'excluir_conta') {
            include 'excluir_conta.php';
        }
    }

} else {
    // Interface padrão para visitantes
    echo '<section class="hero">';
    echo '<h1>Bem-vindo ao Banco de Tintas</h1>';
    echo '<p>Transforme vidas com cores! Doe tintas que você não usa mais ou solicite tintas para suas necessidades.</p>';
    echo '<a href="' . BASE_URL . 'cadastro/usuario.php" class="btn">Cadastrar</a>';
    echo '<button class="btn" onclick="openModal()">Acessar</button>';
    echo '</section>';
}
