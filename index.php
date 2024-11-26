<?php
// Incluir arquivos de configuração e sessões
require_once 'includes/config.php';
session_start();

if (isset($_SESSION['success_message'])) {
    echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']);
}

include './templates/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/styles.css">
    <title>Banco de Tintas</title>
</head>

<body>
    <main class="container">
        <!-- Exibe mensagem de erro no login -->
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Exibe o menu dinâmico se o usuário estiver logado -->
        <?php if (isset($_SESSION['usuario_id'])): ?>
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
                        <li><a href="recebimento/solicita.php">Solicitar Tintas</a></li>
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

        <!-- Modal -->
        <div id="loginModal" class="modal" aria-hidden="true" role="dialog">
            <div class="modal-content">
                <span class="modal-close" onclick="closeModal()">&times;</span>
                <div class="modal-header">
                    <h2>Acessar o Sistema</h2>
                </div>
                <form method="POST" action="login.php" class="modal-form">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" placeholder="Digite seu email" required>
                    <label for="password">Senha:</label>
                    <input type="password" name="password" id="password" placeholder="Digite sua senha" required>
                    <div class="checkbox-group">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Lembrar-me</label>
                    </div>
                    <button type="submit" class="btn">Entrar</button>
                </form>
            </div>
        </div>

        <section class="about">
            <h2>Sobre o Projeto</h2>
            <p>O Banco de Tintas é uma iniciativa que conecta doadores com beneficiários, promovendo a reutilização de
                tintas e
                reduzindo desperdícios. Nosso objetivo é levar cor e vida para aqueles que mais precisam.</p>
        </section>
        <section class="how-it-works">
            <h2>Como Funciona?</h2>
            <div class="steps">
                <div class="step">
                    <h3>1. Cadastre-se</h3>
                    <p>Crie uma conta para doar ou solicitar tintas.</p>
                </div>
                <div class="step">
                    <h3>2. Faça uma Doação</h3>
                    <p>Disponibilize suas tintas para beneficiar outras pessoas ou organizações.</p>
                </div>
                <div class="step">
                    <h3>3. Solicite Tintas</h3>
                    <p>Seja um beneficiário e solicite tintas disponíveis no sistema.</p>
                </div>
            </div>
        </section>
    </main>
    <script src="./SCRIPT/script.js"></script>
    <?php include './templates/footer.php'; ?>