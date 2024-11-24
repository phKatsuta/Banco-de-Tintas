<?php
// Recebe o CEP da requisição GET
if (isset($_GET['cep'])) {
    $cep = $_GET['cep'];
    
    // Chama a função de busca de endereço
    $endereco = buscarEnderecoViaCep($cep);

    // Retorna os dados do endereço em formato JSON
    if ($endereco) {
        echo json_encode($endereco);
    } else {
        echo json_encode(null); // Se não encontrou o endereço
    }
}
