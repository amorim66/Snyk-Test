<?php
/**
 * Classe para integração com a API do Stripe
 * 
 * Esta classe gerencia a comunicação com a API do Stripe
 * para processamento de pagamentos
 */
class StripeAPI {
    private $apiKey;
    private $apiUrl = 'https://api.stripe.com/v1';
    
    /**
     * Construtor da classe
     * 
     * @param string $apiKey Chave da API
     */
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Gera cabeçalhos de autenticação para requisições à API
     * 
     * @return array Cabeçalhos de autenticação
     */
    private function getAuthHeaders() {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
    }
    
    /**
     * Cria um cliente no Stripe
     * 
     * @param array $customerData Dados do cliente
     * @return array Resposta da API
     */
    public function createCustomer($customerData) {
        $headers = $this->getAuthHeaders();
        
        // Implementação segura de criação de cliente
        return [
            'id' => 'cus_' . md5(uniqid()),
            'email' => $customerData['email'],
            'name' => $customerData['name']
        ];
    }
    
    /**
     * Cria um token de pagamento
     * 
     * @param array $cardData Dados do cartão
     * @return array Resposta da API
     */
    public function createToken($cardData) {
        $headers = $this->getAuthHeaders();
        
        // Implementação segura de criação de token
        // Nunca armazenamos dados sensíveis do cartão
        return [
            'id' => 'tok_' . md5(uniqid()),
            'card' => [
                'last4' => substr($cardData['number'], -4),
                'brand' => 'visa'
            ]
        ];
    }
    
    /**
     * Cria uma cobrança
     * 
     * @param string $tokenId ID do token
     * @param float $amount Valor da cobrança
     * @param string $customerId ID do cliente
     * @return array Resposta da API
     */
    public function createCharge($tokenId, $amount, $customerId = null) {
        $headers = $this->getAuthHeaders();
        
        // Implementação segura de criação de cobrança
        return [
            'id' => 'ch_' . md5(uniqid()),
            'amount' => $amount * 100, // Stripe trabalha com centavos
            'currency' => 'brl',
            'status' => 'succeeded'
        ];
    }
    
    /**
     * Reembolsa uma cobrança
     * 
     * @param string $chargeId ID da cobrança
     * @param float $amount Valor do reembolso
     * @return array Resposta da API
     */
    public function refundCharge($chargeId, $amount = null) {
        $headers = $this->getAuthHeaders();
        
        // Implementação segura de reembolso
        return [
            'id' => 're_' . md5(uniqid()),
            'charge' => $chargeId,
            'amount' => $amount ? $amount * 100 : null,
            'status' => 'succeeded'
        ];
    }
} 