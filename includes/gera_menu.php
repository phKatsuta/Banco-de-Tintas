<?php
// Função para obter os tipos de usuário do banco
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

// Função para gerar o menu principal (fora da área de perfil)
function gerarMenuPrincipal($tipos_usuario, $usuario_nome = 'Usuário') {
    echo '<nav class="user-menu">';
    echo '<h2>Bem-vindo, ' . htmlspecialchars($usuario_nome) . '!</h2>';
    echo '<ul>';

    // Menu para Doador
    if (in_array('Doador', $tipos_usuario)) {
        echo '<li><a href="doacao/minhas_doacoes.php">Minhas Doações</a></li>';
        echo '<li><a href="doacao/doacao.php">Cadastrar Doações</a></li>';
    }

    // Menu para Beneficiário
    if (in_array('Beneficiario', $tipos_usuario)) {
        echo '<li><a href="solicitacao/minhas_solicitacoes.php">Minhas Solicitações</a></li>';
        echo '<li><a href="solicitacao/solicitacao.php">Solicitar Tintas</a></li>';
    }

    // Menu para Gestor
    if (in_array('Gestor', $tipos_usuario)) {
        echo '<li><a href="gestao/analise.php">Gestão</a></li>';
        echo '<li><a href="gestao/recebimento.php">Confirmar Doações</a></li>';
    }

    // Menu para Monitor
    if (in_array('Monitor', $tipos_usuario)) {
        echo '<li><a href="gestao/recebimento.php">Confirmar Doações</a></li>';
        echo '<li><a href="gestao/monitores.php">Monitoramento</a></li>';
    }

    echo '</ul>';
    echo '<form method="POST" action="logout.php">';
    echo '<button type="submit" class="btn">Sair</button>';
    echo '</form>';
    echo '</nav>';
}

// Função para gerar o menu de perfil
function gerarMenuPerfil() {
    echo '<nav class="perfil-menu">';
    echo '<h3>Menu de Perfil</h3>';
    echo '<ul>';
    echo '<li><a href="perfil/editar_perfil.php">Editar Perfil</a></li>';
    echo '<li><a href="perfil/adicionar_permissao.php">Adicionar Permissão</a></li>';
    echo '<li><a href="perfil/alterar_para_organizacao.php">Alterar para Organização</a></li>';
    echo '<li><a href="perfil/excluir_conta.php">Excluir Conta</a></li>';
    echo '</ul>';
    echo '</nav>';
}

// Controle de interface para o usuário logado
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $tipos_usuario = getUserTypes($pdo, $usuario_id);
    $usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';

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
    echo '<a href="cadastro/usuario.php" class="btn">Cadastrar</a>';
    echo '<button class="btn" onclick="openModal()">Acessar</button>';
    echo '</section>';
}

