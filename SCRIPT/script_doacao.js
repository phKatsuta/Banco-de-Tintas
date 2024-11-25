document.addEventListener('DOMContentLoaded', function () {
    const usuarioTipo = "<?= $usuario_tipo ?>";
    const doadorField = document.getElementById('id_doador');
    
    // Oculta o campo de seleção de doador se o usuário for Doador
    if (usuarioTipo === 'Doador') {
        doadorField.parentElement.style.display = 'none';
    }
});
