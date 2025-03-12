<?php
/**
 * Classe modelo para pedidos da loja virtual
 */
class Order {
    private $id;
    private $userId;
    private $items;
    private $total;
    private $status;
    private $paymentMethod;
    private $paymentId;
    private $shippingAddress;
    private $createdAt;
    private $updatedAt;
    
    /**
     * Construtor da classe
     * 
     * @param array $data Dados do pedido
     */
    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->userId = $data['user_id'] ?? null;
        $this->items = $data['items'] ?? [];
        $this->total = $data['total'] ?? 0.0;
        $this->status = $data['status'] ?? 'pending';
        $this->paymentMethod = $data['payment_method'] ?? '';
        $this->paymentId = $data['payment_id'] ?? '';
        $this->shippingAddress = $data['shipping_address'] ?? [];
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }
    
    /**
     * Salva o pedido no banco de dados
     * 
     * @param Database $db Conexão com o banco de dados
     * @return bool Sucesso da operação
     */
    public function save($db) {
        // Implementação segura de salvamento no banco de dados
        if ($this->id) {
            // Atualiza pedido existente
            $query = "UPDATE orders SET status = ?, payment_id = ?, updated_at = ? WHERE id = ?";
            $params = [$this->status, $this->paymentId, date('Y-m-d H:i:s'), $this->id];
        } else {
            // Insere novo pedido
            $query = "INSERT INTO orders (user_id, total, status, payment_method, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)";
            $params = [$this->userId, $this->total, $this->status, $this->paymentMethod, $this->createdAt, $this->updatedAt];
        }
        
        // Simulação de execução da query
        return true;
    }
    
    /**
     * Processa o pagamento do pedido
     * 
     * @param mixed $paymentGateway Gateway de pagamento
     * @param array $paymentData Dados do pagamento
     * @return bool Sucesso da operação
     */
    public function processPayment($paymentGateway, $paymentData) {
        // Implementação segura de processamento de pagamento
        if ($this->paymentMethod === 'credit_card') {
            $result = $paymentGateway->createCreditCardTransaction(
                $paymentData['card'],
                $this->total,
                ['name' => $paymentData['name'], 'email' => $paymentData['email']]
            );
        } elseif ($this->paymentMethod === 'boleto') {
            $result = $paymentGateway->createBoleto(
                $this->total,
                ['name' => $paymentData['name'], 'email' => $paymentData['email']]
            );
        } else {
            return false;
        }
        
        if (isset($result['status']) && in_array($result['status'], ['paid', 'waiting_payment'])) {
            $this->paymentId = $result['transaction_id'];
            $this->status = $result['status'] === 'paid' ? 'paid' : 'awaiting_payment';
            return true;
        }
        
        return false;
    }
    
    /**
     * Carrega um pedido do banco de dados pelo ID
     * 
     * @param Database $db Conexão com o banco de dados
     * @param int $id ID do pedido
     * @return Order|null Pedido carregado ou null se não encontrado
     */
    public static function findById($db, $id) {
        // Implementação segura de busca no banco de dados
        // Simulação de pedido encontrado
        return new Order([
            'id' => $id,
            'user_id' => 1,
            'total' => 249.80,
            'status' => 'paid',
            'payment_method' => 'credit_card',
            'payment_id' => 'tr_' . md5($id),
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]);
    }
    
    /**
     * Busca pedidos de um usuário
     * 
     * @param Database $db Conexão com o banco de dados
     * @param int $userId ID do usuário
     * @return array Lista de pedidos
     */
    public static function findByUser($db, $userId) {
        // Implementação segura de busca no banco de dados
        // Simulação de pedidos encontrados
        return [
            new Order(['id' => 1, 'user_id' => $userId, 'total' => 249.80, 'status' => 'paid']),
            new Order(['id' => 2, 'user_id' => $userId, 'total' => 99.90, 'status' => 'shipped'])
        ];
    }
    
    /**
     * Getters e setters
     */
    public function getId() {
        return $this->id;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getItems() {
        return $this->items;
    }
    
    public function addItem($item) {
        $this->items[] = $item;
        $this->recalculateTotal();
    }
    
    public function removeItem($index) {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->recalculateTotal();
        }
    }
    
    private function recalculateTotal() {
        $this->total = 0;
        foreach ($this->items as $item) {
            $this->total += $item['price'] * $item['quantity'];
        }
    }
    
    public function getTotal() {
        return $this->total;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function setStatus($status) {
        $this->status = $status;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }
    
    public function setPaymentMethod($paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }
    
    public function getPaymentId() {
        return $this->paymentId;
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
} 