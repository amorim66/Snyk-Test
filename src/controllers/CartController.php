<?php
/**
 * Controlador de carrinho da loja virtual
 */
class CartController {
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
     * Obtém o carrinho de um usuário
     * 
     * @param int $userId ID do usuário
     * @return array Carrinho do usuário
     */
    public function getCart($userId) {
        // Implementação segura de obtenção de carrinho
        $cart = Cart::loadByUser($this->db, $userId);
        
        return [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal()
        ];
    }
    
    /**
     * Adiciona um produto ao carrinho
     * 
     * @param int $userId ID do usuário
     * @param int $productId ID do produto
     * @param int $quantity Quantidade
     * @return array Carrinho atualizado
     */
    public function addToCart($userId, $productId, $quantity = 1) {
        // Validação de dados
        if ($quantity <= 0) {
            throw new Exception("Quantidade inválida");
        }
        
        // Carrega o carrinho do usuário
        $cart = Cart::loadByUser($this->db, $userId);
        
        // Carrega o produto
        $product = Product::findById($this->db, $productId);
        
        if (!$product) {
            throw new Exception("Produto não encontrado");
        }
        
        // Verifica estoque
        if ($product->getStock() < $quantity) {
            throw new Exception("Estoque insuficiente");
        }
        
        // Adiciona o produto ao carrinho
        $cart->addItem([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice()
        ], $quantity);
        
        // Salva o carrinho
        $cart->save($this->db);
        
        return [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal()
        ];
    }
    
    /**
     * Remove um produto do carrinho
     * 
     * @param int $userId ID do usuário
     * @param int $productId ID do produto
     * @return array Carrinho atualizado
     */
    public function removeFromCart($userId, $productId) {
        // Carrega o carrinho do usuário
        $cart = Cart::loadByUser($this->db, $userId);
        
        // Remove o produto do carrinho
        $cart->removeItem($productId);
        
        // Salva o carrinho
        $cart->save($this->db);
        
        return [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal()
        ];
    }
    
    /**
     * Atualiza a quantidade de um produto no carrinho
     * 
     * @param int $userId ID do usuário
     * @param int $productId ID do produto
     * @param int $quantity Nova quantidade
     * @return array Carrinho atualizado
     */
    public function updateCartItem($userId, $productId, $quantity) {
        // Validação de dados
        if ($quantity < 0) {
            throw new Exception("Quantidade inválida");
        }
        
        // Carrega o carrinho do usuário
        $cart = Cart::loadByUser($this->db, $userId);
        
        if ($quantity === 0) {
            // Remove o produto do carrinho
            $cart->removeItem($productId);
        } else {
            // Carrega o produto para verificar estoque
            $product = Product::findById($this->db, $productId);
            
            if (!$product) {
                throw new Exception("Produto não encontrado");
            }
            
            // Verifica estoque
            if ($product->getStock() < $quantity) {
                throw new Exception("Estoque insuficiente");
            }
            
            // Atualiza a quantidade
            $cart->updateItemQuantity($productId, $quantity);
        }
        
        // Salva o carrinho
        $cart->save($this->db);
        
        return [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal()
        ];
    }
    
    /**
     * Limpa o carrinho de um usuário
     * 
     * @param int $userId ID do usuário
     * @return array Carrinho vazio
     */
    public function clearCart($userId) {
        // Carrega o carrinho do usuário
        $cart = Cart::loadByUser($this->db, $userId);
        
        // Limpa o carrinho
        $cart->clear();
        
        // Salva o carrinho
        $cart->save($this->db);
        
        return [
            'items' => [],
            'total' => 0
        ];
    }
} 