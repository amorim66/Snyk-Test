<?php
/**
 * Classe modelo para carrinho de compras da loja virtual
 */
class Cart {
    private $userId;
    private $items;
    private $total;
    private $createdAt;
    private $updatedAt;
    
    /**
     * Construtor da classe
     * 
     * @param array $data Dados do carrinho
     */
    public function __construct($data = []) {
        $this->userId = $data['user_id'] ?? null;
        $this->items = $data['items'] ?? [];
        $this->total = $data['total'] ?? 0.0;
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }
    
    /**
     * Adiciona um item ao carrinho
     * 
     * @param array $product Produto a ser adicionado
     * @param int $quantity Quantidade
     * @return bool Sucesso da operação
     */
    public function addItem($product, $quantity = 1) {
        // Verifica se o produto já está no carrinho
        foreach ($this->items as &$item) {
            if ($item['product_id'] === $product['id']) {
                $item['quantity'] += $quantity;
                $this->recalculateTotal();
                $this->updatedAt = date('Y-m-d H:i:s');
                return true;
            }
        }
        
        // Adiciona novo item
        $this->items[] = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];
        
        $this->recalculateTotal();
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }
    
    /**
     * Remove um item do carrinho
     * 
     * @param int $productId ID do produto
     * @return bool Sucesso da operação
     */
    public function removeItem($productId) {
        foreach ($this->items as $index => $item) {
            if ($item['product_id'] === $productId) {
                unset($this->items[$index]);
                $this->items = array_values($this->items);
                $this->recalculateTotal();
                $this->updatedAt = date('Y-m-d H:i:s');
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Atualiza a quantidade de um item no carrinho
     * 
     * @param int $productId ID do produto
     * @param int $quantity Nova quantidade
     * @return bool Sucesso da operação
     */
    public function updateItemQuantity($productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($productId);
        }
        
        foreach ($this->items as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity'] = $quantity;
                $this->recalculateTotal();
                $this->updatedAt = date('Y-m-d H:i:s');
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Recalcula o total do carrinho
     */
    private function recalculateTotal() {
        $this->total = 0;
        foreach ($this->items as $item) {
            $this->total += $item['price'] * $item['quantity'];
        }
    }
    
    /**
     * Limpa o carrinho
     */
    public function clear() {
        $this->items = [];
        $this->total = 0;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    /**
     * Salva o carrinho no banco de dados
     * 
     * @param Database $db Conexão com o banco de dados
     * @return bool Sucesso da operação
     */
    public function save($db) {
        // Implementação segura de salvamento no banco de dados
        // Simulação de execução da query
        return true;
    }
    
    /**
     * Carrega o carrinho de um usuário
     * 
     * @param Database $db Conexão com o banco de dados
     * @param int $userId ID do usuário
     * @return Cart Carrinho carregado
     */
    public static function loadByUser($db, $userId) {
        // Implementação segura de busca no banco de dados
        // Simulação de carrinho encontrado
        return new Cart([
            'user_id' => $userId,
            'items' => [
                [
                    'product_id' => 1,
                    'name' => 'Produto Exemplo',
                    'price' => 99.90,
                    'quantity' => 2
                ]
            ],
            'total' => 199.80
        ]);
    }
    
    /**
     * Converte o carrinho em um pedido
     * 
     * @return Order Pedido gerado
     */
    public function toOrder() {
        return new Order([
            'user_id' => $this->userId,
            'items' => $this->items,
            'total' => $this->total,
            'status' => 'pending'
        ]);
    }
    
    /**
     * Getters
     */
    public function getUserId() {
        return $this->userId;
    }
    
    public function getItems() {
        return $this->items;
    }
    
    public function getTotal() {
        return $this->total;
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    public function getUpdatedAt() {
        return $this->updatedAt;
    }
} 