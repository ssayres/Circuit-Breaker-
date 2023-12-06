<?php

header("Access-Control-Allow-Origin: *");

class CircuitBreaker {
    private $failureThreshold = 3; // Limite de falhas consecutivas
    private $failureCount = 0; // Contador de falhas
    
    // Método para verificar se o serviço está funcionando normalmente
    public function isServiceAvailable($cnpj) {

        $url = 'https://www.receitaws.com.br/v1/cnpj/' . $cnpj;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Defina um tempo limite para a requisição

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Verifique se a resposta foi bem-sucedida (código 200) e contém dados válidos
        if ($httpCode == 200 && !empty($response)) {
            // Se a resposta é 200 e tem dados, o serviço está disponível
            return $response; // Retorna a resposta para ser utilizada fora do método
        } else {
            // Se não, o serviço pode estar fora do ar ou ocorreu um erro na requisição
            return false;
        }
    }

    // Método para registrar uma falha no serviço
    public function registerFailure() {
        $this->failureCount++;
    
        if ($this->failureCount >= $this->failureThreshold) {
            sleep(15); 
            $this->resetFailureCount(); 
        }
    }

    // Método para resetar o contador de falhas
    public function resetFailureCount() {
        $this->failureCount = 0;
    }

    // Método para verificar se o circuit breaker deve ser acionado
    public function shouldTripCircuit() {
        return $this->failureCount >= $this->failureThreshold;
    }
}

$circuitBreaker = new CircuitBreaker();
$cnpj = "31565104025838";
$isAvailable = $circuitBreaker->isServiceAvailable($cnpj);

if ($isAvailable) {
    echo 'O serviço está disponível para o CNPJ informado<br>';
    $responseArray = json_decode($isAvailable, true); // Decodifica o JSON para um array associativo

    // Verifica se o JSON foi decodificado corretamente e se possui informações
    if ($responseArray && !empty($responseArray)) {
        //  como acessar algumas informações do CNPJ
        echo 'Nome: ' . $responseArray['nome'] . '<br>';
        echo 'Atividade principal: ' . $responseArray['atividade_principal'][0]['text'] . '<br>';
        echo 'Situação cadastral: ' . $responseArray['situacao'] . '<br>';
        // Adicione mais informações conforme necessário
    } else {
        echo 'Resposta vazia ou inválida';
    }
} else {
    echo 'O serviço não está disponível para o CNPJ informado ou ocorreu um erro na requisição.';
}


/*
Como funciona:

A classe CircuitBreaker possui métodos que realizam a verificação do serviço externo (isServiceAvailable) e
 outras funcionalidades relacionadas ao controle de falhas e limite de tentativas (registerFailure, resetFailureCount, shouldTripCircuit).
Verificação do serviço:

O método isServiceAvailable realiza uma requisição HTTP para a API da ReceitaWS usando cURL.
Ele verifica se a resposta HTTP é bem-sucedida (código 200) e se há dados válidos na resposta.
Retorna true se o serviço estiver disponível ou false se houver um erro na requisição ou a resposta estiver vazia.
Exibição de informações do CNPJ:

Se o serviço estiver disponível, a resposta da API (um JSON) é decodificada para um array associativo usando json_decode.
As informações desse array são acessadas e exibidas no código, por exemplo, o nome da empresa, atividade principal e situação cadastral.
Controle de fluxo:

Há uma verificação adicional para lidar com respostas vazias ou inválidas, exibindo uma mensagem apropriada.
Em resumo, o código verifica se a API da ReceitaWS está acessível, realiza a requisição, trata a resposta JSON (caso esteja disponível) e exibe informações específicas do CNPJ. A utilização do circuit breaker ajuda a controlar o acesso ao serviço externo e a lidar com falhas de requisição.

Em PHP, cURL (Client URL) 
é uma biblioteca que permite fazer requisições para diferentes tipos de servidores usando vários protocolos, 
como HTTP, HTTPS, FTP, entre outros. 
Ela oferece uma interface para transferência de dados com suporte a uma variedade de opções e configurações.

O cURL opera por meio de funções específicas, 
permitindo configurar a requisição com diversos parâmetros,
como URL alvo, método de requisição, cabeçalhos HTTP, tempo limite, 
autenticação, entre outros.
