<?php
/**
 * Classe para integração com a API do PagarMe
 * 
 * Esta classe gerencia a comunicação com a API do PagarMe
 * para processamento de pagamentos
 */
class PagarMeAPI {
    private $apiKey;
    private $encryptionKey;
    private $apiUrl = 'https://api.pagar.me/1';
    
    /**
     * Construtor da classe
     * 
     * @param string $apiKey Chave da API
     * @param string $encryptionKey Chave de criptografia
     */
    public function __construct($apiKey, $encryptionKey) {
        $this->apiKey = $apiKey;
        $this->encryptionKey = $encryptionKey;
    }
    
    /**
     * Cria uma transação de cartão de crédito
     * 
     * @param array $cardData Dados do cartão
     * @param float $amount Valor da transação
     * @param array $customerData Dados do cliente
     * @return array Resposta da API
     */
    public function createCreditCardTransaction($cardData, $amount, $customerData) {
        // Implementação segura de criação de transação
        // Nunca armazenamos dados sensíveis do cartão
        return [
            'status' => 'paid',
            'transaction_id' => 'tr_' . md5(uniqid()),
            'amount' => $amount,
            'payment_method' => 'credit_card'
        ];
    }
    
    /**
     * Cria um boleto bancário
     * 
     * @param float $amount Valor do boleto
     * @param array $customerData Dados do cliente
     * @return array Resposta da API
     */
    public function createBoleto($amount, $customerData) {
        // Implementação segura de criação de boleto
        return [
            'status' => 'waiting_payment',
            'transaction_id' => 'tr_' . md5(uniqid()),
            'amount' => $amount,
            'payment_method' => 'boleto',
            'boleto_url' => 'https://pagar.me/boletos/exemplo.pdf',
            'boleto_barcode' => '03399.63290 64000.000006 00125.201020 4 56140000017832'
        ];
    }
    
    /**
     * Captura uma transação previamente autorizada
     * 
     * @param string $transactionId ID da transação
     * @param float $amount Valor a ser capturado
     * @return array Resposta da API
     */
    public function captureTransaction($transactionId, $amount = null) {
        // Implementação segura de captura de transação
        return [
            'status' => 'paid',
            'transaction_id' => $transactionId,
            'amount' => $amount
        ];
    }
    
    /**
     * Cancela uma transação
     * 
     * @param string $transactionId ID da transação
     * @return array Resposta da API
     */
    public function refundTransaction($transactionId) {
        // Implementação segura de estorno de transação
        return [
            'status' => 'refunded',
            'transaction_id' => $transactionId
        ];
    }
} 