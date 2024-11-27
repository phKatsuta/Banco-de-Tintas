<?php
// Incluir arquivos de configuração e sessões
if (isset($_SESSION['success_message'])) {
    echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']);
}

include './templates/header.php'; // Incluindo o cabeçalho
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Banco de Tintas Orgânicas">
    <meta name="keywords" content="banco de tintas, sustentabilidade, FATEC Jundiaí">
    <title>Banco de Tintas</title>

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="shortcut icon"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.css"/>
    <link rel="stylesheet" href="css/animate.css"/>
    <link rel="stylesheet" href="css/style.css"/>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="./CSS/styles.css">
    <link rel="stylesheet" href="./CSS/styles_menu.css">

</head>

<body>
    <!-- Page Preloader -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <!-- Exibe mensagem de erro no login -->
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Exibe o menu dinâmico se o usuário estiver logado -->
        <?php require_once 'includes/gera_menu.php'; ?>

        <!-- Modal de Login -->
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

        <!-- Sobre o Projeto -->
        <section class="about">
            <h2>Sobre o Projeto</h2>
            <p>O Banco de Tintas é uma iniciativa que conecta doadores com beneficiários, promovendo a reutilização de tintas e
                reduzindo desperdícios. Nosso objetivo é levar cor e vida para aqueles que mais precisam.</p>
        </section>

        <!-- Como Funciona? -->
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

        <!-- Latest News Section -->
        <div class="latest-news-section">
            <div class="ln-title">Atualizações</div>
            <div class="news-ticker">
                <div class="news-ticker-contant">
                    <div class="nt-item"><span class="new">Novo</span>Alunos de ADS da FATEC Jundiaí concluem projeto de banco de tintas orgânicas com sistema completo e sustentável.</div>
                    <div class="nt-item"><span class="strategy">Estratégia</span>FATEC aplica estratégia inovadora para criar um banco de tintas orgânicas sustentável e funcional.</div>
                    <div class="nt-item"><span class="racing">Projeto</span>Scripts e automação de processos garantem a funcionalidade do banco de tintas orgânicas desenvolvido por alunos de ADS </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer Section -->
    <footer class="footer-section">
        <div class="container">
            <ul class="footer-menu">
                <li><a href="index.html">Home</a></li>
                <li><a href="review.html">Banco de Tintas</a></li>
                <li><a href="contact.html">Contato</a></li>
            </ul>
            <p class="copyright">
                Copyright &copy;<script>document.write(new Date().getFullYear());</script> Todos os direitos reservados | Este template foi feito por <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.marquee.min.js"></script>
    <script src="js/main.js"></script>

    <?php include './templates/footer.php'; ?>  <!-- Incluindo o rodapé -->
</body>

</html>
