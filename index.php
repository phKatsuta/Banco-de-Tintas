<?php
// Incluir arquivos de configuração e sessões
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
    <link rel="stylesheet" href="./CSS/styles_menu.css">
    <title>Banco de Tintas</title>
</head>

<body>
    <main class="container">
        <!-- Exibe mensagem de erro no login -->
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Exibe o menu dinâmico se o usuário estiver logado -->
        <?php require_once 'includes/gera_menu.php'; ?>

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