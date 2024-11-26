<?php
// Esta página armazena o menu dinâmico e as permissões dos usuários logados no sistema
if (isset($_SESSION['usuario_id'])): ?>
    <nav class="user-menu">
        <h2>Bem-vindo, <?php echo htmlspecialchars($user['usuario_nome'] ?? 'Usuário'); ?>!</h2>
        <ul>
            <?php if (in_array('Doador', $_SESSION['user_types'])): ?>
                <li><a href="doacao/minhas_doacoes.php">Minhas Doações</a></li>
            <?php endif; ?> <!--- Página para verificar doações cadastradas --->
            <?php if (in_array('Doador', $_SESSION['user_types'])): ?>
                <li><a href="doacao/doacao.php">Cadastrar Doações</a></li>
            <?php endif; ?> <!--- Página para cadastrar doações ---> 
            <?php if (in_array('Beneficiario', $_SESSION['user_types'])): ?>
                <li><a href="solicitacao/minhas_solicitacoes.php">Minhas Solicitações</a></li>
                <?php endif; ?> <!--- Página para verificar solicitações cadastradas --->
            <?php if (in_array('Beneficiario', $_SESSION['user_types'])): ?>
                <li><a href="solicitacao/solicitacao.php">Solicitar Tintas</a></li>
            <?php endif; ?> <!--- Página para solicitar tintas --->
            <?php if (in_array('Gestor', $_SESSION['user_types'])): ?>
                <li><a href="gestao/analise.php">Gestão</a></li>
            <?php endif; ?>
            <?php if (in_array('Gestor', $_SESSION['user_types']) || in_array('Monitor', $_SESSION['user_types'])): ?>
                <li><a href="gestao/recebimento.php">Confirmar doações</a></li>
            <?php endif; ?> <!--- Página para Confirmar doações --->
            <?php if (in_array('Monitor', $_SESSION['user_types'])): ?>
                <li><a href="gestao/monitores.php">Monitoramento</a></li>
            <?php endif; ?>
        </ul>
        <form method="POST" action="logout.php">
            <button type="submit" class="btn">Sair</button>
        </form>
    </nav>
<?php else: ?>
    <!-- Exibe a interface padrão para visitantes -->
    <section class="hero">
        <h1>Bem-vindo ao Banco de Tintas</h1>
        <p>Transforme vidas com cores! Doe tintas que você não usa mais ou solicite tintas para suas necessidades.
        </p>
        <a href="cadastro/usuario.php" class="btn">Cadastrar</a>
        <button class="btn" onclick="openModal()">Acessar</button>
    </section>
<?php endif; ?>
