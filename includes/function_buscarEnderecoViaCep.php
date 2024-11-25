<?php
// Função para buscar dados do endereço usando o CEP via API ViaCEP
function buscarEnderecoViaCep($cep) {
    // Remover caracteres não numéricos (como traços) do CEP
    $cep = preg_replace("/[^0-9]/", "", $cep);

    // Verifica se o CEP tem o formato correto (8 dígitos numéricos)
    if (strlen($cep) != 8) {
        return null; // CEP inválido
    }

    // URL da API ViaCEP
    $url = "https://viacep.com.br/ws/{$cep}/json/";

    // Faz a requisição para a API ViaCEP
    $response = file_get_contents($url);

    // Se não houver resposta, retorna null
    if (!$response) {
        return null;
    }

    // Decodifica a resposta JSON
    $dados = json_decode($response, true);

    // Verifica se o retorno da API contém os dados esperados
    if (isset($dados['erro']) && $dados['erro'] == true) {
        return null; // Caso o CEP seja inválido ou não encontrado
    }

    return $dados;
}