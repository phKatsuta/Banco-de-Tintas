<?php
session_start();  // Iniciar a sessão antes de qualquer saída HTML
require 'includes/config.php';

$error = ''; // Inicialize a variável de erro

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verifica o usuário
    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE usuario_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['senha_hash'])) {
        // Define a sessão
        $_SESSION['usuario_id'] = $user['id'];

        // Obtém os tipos de usuário
        $stmt = $pdo->prepare("SELECT tipo FROM Usuario_Tipos WHERE usuario_id = ?");
        $stmt->execute([$user['id']]);
        $user_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $_SESSION['user_types'] = $user_types;

        // Redireciona para a página home.php após sucesso no login
        header("Location: http://localhost/Banco-de-Tintas/index.php");
        exit;
    } else {
        $error = "Email ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Game Warrior Template</title>
    <meta charset="UTF-8">
    <meta name="description" content="Game Warrior Template">
    <meta name="keywords" content="warrior, game, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->   
    <link href="img/favicon.ico" rel="shortcut icon"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.css"/>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/animate.css"/>

    <style>
        /* Ajustes de padding para o layout */
        header.header-section {
            position: relative;
            z-index: 10; 
        }

        .page-info-section {
            padding-top: 120px; 
        }

        .pi-content {
            padding-top: 0px; 
        }

        .login-form {
            max-width: 400px;
            margin: 20px auto 0;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 8px;
            color: white;
        }

        .login-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .login-form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .login-form button:hover {
            background-color: #0056b3;
        }

        .register-link {
            text-align: center;
            margin-top: 10px;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <!-- Header section -->
    <header class="header-section">
        <div class="container">
            <a class="site-logo" href="index.html">
                <img src="img/logo.png" alt="">
            </a>
            <div class="user-panel">
                <a href="single-blog.html">Login</a>
                <br>
            </div>
            <div class="user-panel">
                <a href="categories.html">Cadastro</a>
            </div>
            <div class="nav-switch">
                <i class="fa fa-bars"></i>
            </div>
            <nav class="main-menu">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="review.html">Banco de Tintas</a></li>                    
                    <li><a href="contact.html">Contato</a></li>
                    <li><a href="community.html">Sobre o Projeto</a></li>        
                </ul>
            </nav>
        </div>
    </header>

    <!-- Page info section -->
    <section class="page-info-section set-bg" data-setbg="img/page-top-bg/2.jpg">
        <div class="pi-content">
            <div class="container">
                <div class="row">
                    <!-- Formulário de Login Centralizado -->
                    <div class="col-md-12">
                        <div class="login-form">
                            <h2>Login</h2>
                            <form method="POST" action="login.php">
                                <label>Email:</label>
                                <input type="email" name="email" required placeholder="Digite seu email">
                                 
                                <label>Senha:</label>
                                <input type="password" name="password" required placeholder="Digite sua senha">
                                 
                                <button type="submit">Entrar</button>
                            </form>

                            <?php if ($error): ?>
                                <div class="error-message">
                                    <p><?php echo $error; ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="register-link">
                                <p>Não tem uma conta? <a href="categories.html" style="color: #007bff;">Cadastre-se aqui</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer section -->
    <footer class="footer-section">
        <div class="container">
            <ul class="footer-menu">
                <li><a href="index.html">Home</a></li>
                <li><a href="review.html">Games</a></li>
                <li><a href="categories.html">Blog</a></li>
                <li><a href="community.html">Forums</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
            <p class="copyright">
                Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>
            </p>
        </div>
    </footer>

    <!-- Javascripts -->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.marquee.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
