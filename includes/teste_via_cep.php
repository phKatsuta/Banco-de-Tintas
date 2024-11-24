<?php
function testarViaCep($cep) {
    // Remove caracteres não numéricos
    $cep = preg_replace("/[^0-9]/", "", $cep);
    
    // URL da API
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    
    // Faz a requisição
    $response = file_get_contents($url);

    // Verifica se houve resposta
    if (!$response) {
        return "Erro: Não foi possível acessar a API do ViaCEP.";
    }

    // Decodifica o JSON
    $dados = json_decode($response, true);

    // Verifica se há erro na resposta
    if (isset($dados['erro']) && $dados['erro'] == true) {
        return "Erro: CEP inválido ou não encontrado.";
    }

    // Retorna os dados do endereço
    return $dados;
}

// Teste com um CEP válido
$cep = "01001000"; // Exemplo de CEP válido
$resultado = testarViaCep($cep);

// Exibe o resultado
echo "<pre>";
print_r($resultado);
echo "</pre>";
?>
