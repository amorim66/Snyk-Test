<?php
/**
 * Controlador de pedidos da loja virtual
 */
class OrderController {
    private $db;
    
    /**
     * Construtor da classe
     * 
     * @param Database $db Conexão com o banco de dados
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Lista pedidos de um usuário
     * 
     * @param int $userId ID do usuário
     * @param array $filters Filtros de busca
     * @return array Lista de pedidos
     */
    public function listOrders($userId, $filters = []) {
        // Implementação segura de listagem de pedidos
        $orders = Order::findByUser($this->db, $userId);
        
        $result = [];
        foreach ($orders as $order) {
            $result[] = [
                'id' => $order->getId(),
                'total' => $order->getTotal(),
                'status' => $order->getStatus(),
                'created_at' => $order->getCreatedAt()
            ];
        }
        
        return $result;
    }
    
    /**
     * Obtém detalhes de um pedido
     * 
     * @param int $id ID do pedido
     * @param int $userId ID do usuário (para verificação de acesso)
     * @return array|null Detalhes do pedido ou null se não encontrado
     */
    public function getOrder($id, $userId = null) {
        // Implementação segura de obtenção de pedido
        $order = Order::findById($this->db, $id);
        
        if (!$order) {
            return null;
        }
        
        // Verifica se o pedido pertence ao usuário
        if ($userId !== null && $order->getUserId() !== $userId) {
            return null;
        }
        
        return [
            'id' => $order->getId(),
            'user_id' => $order->getUserId(),
            'items' => $order->getItems(),
            'total' => $order->getTotal(),
            'status' => $order->getStatus(),
            'payment_method' => $order->getPaymentMethod(),
            'payment_id' => $order->getPaymentId(),
            'created_at' => $order->getCreatedAt()
        ];
    }
    
    /**
     * Cria um novo pedido a partir do carrinho
     * 
     * @param int $userId ID do usuário
     * @param array $paymentData Dados de pagamento
     * @return array Pedido criado
     */
    public function createOrder($userId, $paymentData) {
        // Carrega o carrinho do usuário
        $cart = Cart::loadByUser($this->db, $userId);
        
        if (count($cart->getItems()) === 0) {
            throw new Exception("Carrinho vazio");
        }
        
        // Cria o pedido a partir do carrinho
        $order = $cart->toOrder();
        
        // Define o método de pagamento
        if (!isset($paymentData['method'])) {
            throw new Exception("Método de pagamento não informado");
        }
        
        $order->setPaymentMethod($paymentData['method']);
        
        // Processa o pagamento
        switch ($paymentData['method']) {
            case 'credit_card':
                $paymentGateway = new PagarMeAPI('api_key', 'encryption_key');
                break;
            case 'boleto':
                $paymentGateway = new PagarMeAPI('api_key', 'encryption_key');
                break;
            case 'stripe':
                $paymentGateway = new StripeAPI('api_key');
                break;
            default:
                throw new Exception("Método de pagamento não suportado");
        }
        
        $success = $order->processPayment($paymentGateway, $paymentData);
        
        if (!$success) {
            throw new Exception("Falha no processamento do pagamento");
        }
        
        // Salva o pedido
        $order->save($this->db);
        
        // Limpa o carrinho
        $cart->clear();
        $cart->save($this->db);
        
        return [
            'id' => $order->getId(),
            'total' => $order->getTotal(),
            'status' => $order->getStatus(),
            'payment_method' => $order->getPaymentMethod(),
            'payment_id' => $order->getPaymentId()
        ];
    }
    
    /**
     * Atualiza o status de um pedido
     * 
     * @param int $id ID do pedido
     * @param string $status Novo status
     * @return array Pedido atualizado
     */
    public function updateOrderStatus($id, $status) {
        // Implementação segura de atualização de status
        $order = Order::findById($this->db, $id);
        
        if (!$order) {
            throw new Exception("Pedido não encontrado");
        }
        
        $validStatuses = ['pending', 'paid', 'shipped', 'delivered', 'canceled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Status inválido");
        }
        
        $order->setStatus($status);
        $order->save($this->db);
        
        return [
            'id' => $order->getId(),
            'status' => $order->getStatus()
        ];
    }
    
    /**
     * Cancela um pedido
     * 
     * @param int $id ID do pedido
     * @param int $userId ID do usuário (para verificação de acesso)
     * @return array Pedido cancelado
     */
    public function cancelOrder($id, $userId = null) {
        // Implementação segura de cancelamento de pedido
        $order = Order::findById($this->db, $id);
        
        if (!$order) {
            throw new Exception("Pedido não encontrado");
        }
        
        // Verifica se o pedido pertence ao usuário
        if ($userId !== null && $order->getUserId() !== $userId) {
            throw new Exception("Acesso negado");
        }
        
        // Verifica se o pedido pode ser cancelado
        if (in_array($order->getStatus(), ['shipped', 'delivered'])) {
            throw new Exception("Pedido não pode ser cancelado");
        }
        
        // Se o pedido foi pago, realiza o estorno
        if ($order->getStatus() === 'paid' && $order->getPaymentId()) {
            switch ($order->getPaymentMethod()) {
                case 'credit_card':
                    $paymentGateway = new PagarMeAPI('api_key', 'encryption_key');
                    $paymentGateway->refundTransaction($order->getPaymentId());
                    break;
                case 'stripe':
                    $paymentGateway = new StripeAPI('api_key');
                    $paymentGateway->refundCharge($order->getPaymentId());
                    break;
            }
        }
        
        $order->setStatus('canceled');
        $order->save($this->db);
        
        return [
            'id' => $order->getId(),
            'status' => $order->getStatus()
        ];
    }
} 