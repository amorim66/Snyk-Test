<?php
/**
 * Classe modelo para produtos da loja virtual
 */
class Product {
    private $id;
    private $name;
    private $description;
    private $price;
    private $stock;
    private $categories;
    private $images;
    private $sku;
    
    /**
     * Construtor da classe
     * 
     * @param array $data Dados do produto
     */
    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->price = $data['price'] ?? 0.0;
        $this->stock = $data['stock'] ?? 0;
        $this->categories = $data['categories'] ?? [];
        $this->images = $data['images'] ?? [];
        $this->sku = $data['sku'] ?? '';
    }
    
    /**
     * Salva o produto no banco de dados
     * 
     * @param Database $db Conexão com o banco de dados
     * @return bool Sucesso da operação
     */
    public function save($db) {
        // Implementação segura de salvamento no banco de dados
        if ($this->id) {
            // Atualiza produto existente
            $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, sku = ? WHERE id = ?";
            $params = [$this->name, $this->description, $this->price, $this->stock, $this->sku, $this->id];
        } else {
            // Insere novo produto
            $query = "INSERT INTO products (name, description, price, stock, sku) VALUES (?, ?, ?, ?, ?)";
            $params = [$this->name, $this->description, $this->price, $this->stock, $this->sku];
        }
        
        // Simulação de execução da query
        return true;
    }
    
    /**
     * Carrega um produto do banco de dados pelo ID
     * 
     * @param Database $db Conexão com o banco de dados
     * @param int $id ID do produto
     * @return Product|null Produto carregado ou null se não encontrado
     */
    public static function findById($db, $id) {
        // Implementação segura de busca no banco de dados
        // Simulação de produto encontrado
        return new Product([
            'id' => $id,
            'name' => 'Produto Exemplo',
            'description' => 'Descrição do produto exemplo',
            'price' => 99.90,
            'stock' => 10,
            'sku' => 'SKU' . $id
        ]);
    }
    
    /**
     * Busca produtos por categoria
     * 
     * @param Database $db Conexão com o banco de dados
     * @param int $categoryId ID da categoria
     * @return array Lista de produtos
     */
    public static function findByCategory($db, $categoryId) {
        // Implementação segura de busca no banco de dados
        // Simulação de produtos encontrados
        return [
            new Product(['id' => 1, 'name' => 'Produto 1', 'price' => 99.90]),
            new Product(['id' => 2, 'name' => 'Produto 2', 'price' => 149.90])
        ];
    }
    
    /**
     * Getters e setters
     */
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function getPrice() {
        return $this->price;
    }
    
    public function setPrice($price) {
        $this->price = $price;
    }
    
    public function getStock() {
        return $this->stock;
    }
    
    public function setStock($stock) {
        $this->stock = $stock;
    }
    
    public function getSku() {
        return $this->sku;
    }
    
    public function setSku($sku) {
        $this->sku = $sku;
    }
} 