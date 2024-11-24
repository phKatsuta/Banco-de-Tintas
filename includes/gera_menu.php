<?php
// Função para gerar menu com base nos tipos de usuário
function gerarMenu($tipos_usuario)
{
    echo '<nav>';
    echo '<ul>';
    if (in_array('Gestor', $tipos_usuario)) {
        echo '<li><a href="gestor_dashboard.php">Painel do Gestor</a></li>';
    }
    if (in_array('Monitor', $tipos_usuario)) {
        echo '<li><a href="monitor_dashboard.php">Painel do Monitor</a></li>';
    }
    if (in_array('Doador', $tipos_usuario)) {
        echo '<li><a href="doador_dashboard.php">Minhas Doações</a></li>';
    }
    echo '<li><a href="logout.php">Sair</a></li>';
    echo '</ul>';
    echo '</nav>';
}