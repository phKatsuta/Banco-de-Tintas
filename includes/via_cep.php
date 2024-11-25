<?php
/*
API ViaCEP
URL base: https://viacep.com.br/ws/
Formato de requisição: https://viacep.com.br/ws/{CEP}/json/
    Exemplo: https://viacep.com.br/ws/01001000/json/
Retorna um JSON com os dados do endereço (como logradouro, bairro, cidade, etc.).
*/

function buscarCep($cep)
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cep'])) {
        $cep = preg_replace('/[^0-9]/', '', $_GET['cep']); // Remove caracteres não numéricos
        if (strlen($cep) === 8) {
            $url = "https://viacep.com.br/ws/{$cep}/json/";

            // Faz a requisição à API ViaCEP
            $response = file_get_contents($url);
            try {
                if ($response !== false) {
                    $data = json_decode($response, true);

                    if (isset($data['erro']) && $data['erro'] === true) {
                        echo json_encode(['error' => 'CEP não encontrado.']);
                    } else {
                        echo json_encode($data);
                    }
                } else {
                    echo json_encode(['error' => 'Erro ao conectar à API ViaCEP.']);
                }
            } catch (JsonException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao processar a resposta da API ViaCEP: ' . $e->getMessage()]);
                // Registrar o erro em um log
                error_log("Erro ao processar resposta da API ViaCEP: " . $e->getMessage());
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro inesperado: ' . $e->getMessage()]);
                // Registrar o erro em um log
                error_log("Erro inesperado: " . $e->getMessage());
            }
        } else {
            echo json_encode(['error' => 'CEP inválido.']);
        }
    } else {
        echo json_encode(['error' => 'Requisição inválida.']);
    }

}

// A função só será executada quando chamada via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cep'])) {
    $cep = $_GET['cep'];
    buscarCep($cep);
}
