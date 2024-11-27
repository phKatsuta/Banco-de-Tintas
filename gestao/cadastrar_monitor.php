<?php
require_once '../includes/verifica_gestor.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}
include '../templates/header.php';
?>
    <h2>Por favor, insira as informações</h2>
    <form method="post" action="cadastro_monitor_submit.php">
        <label for="ra">RA:</label>
        <input type="text" id="ra" name="ra" required><br><br>

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required><br><br>

        <label for="sobrenome">Sobrenome:</label>
        <input type="text" id="sobrenome" name="sobrenome" required><br><br>

        <label for="curso">Curso:</label>
        <select name="curso" id="curso" required>
            <option value="" disabled selected>--Selecione o curso--</option>
            <option value="ads">ADS - Análise e Desenvolvimento de Sistemas</option>
            <option value="cd">CD - Ciência de Dados</option>
            <option value="cd">DC - Defesa Cibernética</option>
            <option value="eve">EVE - Eventos</option>
            <option value="gam">GAM - Gestão Ambiental</option>
            <option value="gli">GLI - Gestão de Logística Integrada</option>
            <option value="gti">GTI - Gestão da TI</option>
            <option value="log">LOG - Logística</option>
            <option value="se">SE - Sistemas Embarcados</option>
        </select>
        <br><br>
        <label for="email">email:</label>
        <input type="text" id="email" name="email" required><br><br>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required><br><br>

        <label for="confirmar_senha">Confirmar Senha:</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required><br><br>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone[]" placeholder="(##) #####-####" oninput="mascaraTelefone(this)" required>
        <button type="button" onclick="adicionarTelefone()">Adicionar Telefone</button>
        <div id="telefones"></div>

        <button type="submit">Cadastrar</button>
    </form>

    <script>
        function adicionarTelefone() {
            var divTelefones = document.getElementById("telefones");
            var novoTelefone = document.createElement("input");
            novoTelefone.type = "text";
            novoTelefone.name = "telefone[]";
            divTelefones.appendChild(novoTelefone);
        }
        function mascaraTelefone(elemento) {
            var valor = elemento.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            valor = valor.replace(/^(\d{2})(\d)/g, "($1) $2"); // Coloca parênteses nos dois primeiros dígitos
            valor = valor.replace(/(\d)(\d{4})$/g, "$1-$2"); // Coloca hífen entre o quarto e o quinto dígitos

            elemento.value = valor;
        }

        var inputTelefone = document.getElementById('telefone');
        inputTelefone.addEventListener('input', function () {
            mascaraTelefone(this);
        });
    </script>
</body>

</html>