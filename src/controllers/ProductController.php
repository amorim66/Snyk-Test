<?php
/**
 * Controlador de produtos da loja virtual
 */
class ProductController {
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
     * Lista todos os produtos
     * 
     * @param array $filters Filtros de busca
     * @return array Lista de produtos
     */
    public function listProducts($filters = []) {
        // Implementação segura de listagem de produtos
        $products = [];
        
        // Simulação de produtos
        for ($i = 1; $i <= 10; $i++) {
            $products[] = [
                'id' => $i,
                'name' => "Produto {$i}",
                'description' => "Descrição do produto {$i}",
                'price' => 99.90 + ($i * 10),
                'stock' => rand(5, 20),
                'sku' => "SKU{$i}"
            ];
        }
        
        return $products;
    }
    
    /**
     * Obtém detalhes de um produto
     * 
     * @param int $id ID do produto
     * @return array|null Detalhes do produto ou null se não encontrado
     */
    public function getProduct($id) {
        // Implementação segura de obtenção de produto
        $product = Product::findById($this->db, $id);
        
        if ($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'sku' => $product->getSku()
            ];
        }
        
        return null;
    }
    
    /**
     * Cria um novo produto
     * 
     * @param array $data Dados do produto
     * @return array Produto criado
     */
    public function createProduct($data) {
        // Validação de dados
        if (empty($data['name']) || empty($data['price'])) {
            throw new Exception("Nome e preço são obrigatórios");
        }
        
        // Implementação segura de criação de produto
        $product = new Product($data);
        $product->save($this->db);
        
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'sku' => $product->getSku()
        ];
    }
    
    /**
     * Atualiza um produto existente
     * 
     * @param int $id ID do produto
     * @param array $data Dados do produto
     * @return array Produto atualizado
     */
    public function updateProduct($id, $data) {
        // Implementação segura de atualização de produto
        $product = Product::findById($this->db, $id);
        
        if (!$product) {
            throw new Exception("Produto não encontrado");
        }
        
        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        
        if (isset($data['stock'])) {
            $product->setStock($data['stock']);
        }
        
        if (isset($data['sku'])) {
            $product->setSku($data['sku']);
        }
        
        $product->save($this->db);
        
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'sku' => $product->getSku()
        ];
    }
    
    /**
     * Remove um produto
     * 
     * @param int $id ID do produto
     * @return bool Sucesso da operação
     */
    public function deleteProduct($id) {
        // Implementação segura de remoção de produto
        // Simulação de remoção
        return true;
    }
    
    /**
     * Sincroniza produtos com marketplaces
     * 
     * @param array $productIds IDs dos produtos
     * @param array $marketplaces Marketplaces para sincronizar
     * @return array Resultado da sincronização
     */
    public function syncWithMarketplaces($productIds, $marketplaces) {
        $results = [];
        
        foreach ($productIds as $productId) {
            $product = $this->getProduct($productId);
            
            if (!$product) {
                $results[$productId] = ['status' => 'error', 'message' => 'Produto não encontrado'];
                continue;
            }
            
            foreach ($marketplaces as $marketplace) {
                switch ($marketplace) {
                    case 'mercado_livre':
                        $api = new MercadoLivreAPI('client_id', 'client_secret');
                        $result = $api->publishProduct($product);
                        break;
                    case 'amazon':
                        $api = new AmazonAPI('seller_id', 'access_key', 'secret_key');
                        $result = $api->publishProduct($product);
                        break;
                    case 'shopee':
                        $api = new ShopeeAPI('partner_id', 'partner_key', 'shop_id');
                        $result = $api->publishProduct($product);
                        break;
                    case 'magalu':
                        $api = new MagaluAPI('api_key', 'seller_id');
                        $result = $api->publishProduct($product);
                        break;
                    default:
                        $result = ['status' => 'error', 'message' => 'Marketplace não suportado'];
                }
                
                $results[$productId][$marketplace] = $result;
            }
        }
        
        return $results;
    }
} 