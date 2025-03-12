<?php
/**
 * API para processamento de pagamentos
 * 
 * Este arquivo fornece endpoints para processamento de pagamentos
 */

// Inicializa a sessão
session_start();

// Inclui arquivos necessários
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Authentication.php';
require_once __DIR__ . '/../../src/models/Order.php';
require_once __DIR__ . '/../../src/controllers/OrderController.php';
require_once __DIR__ . '/../../src/integrations/payments/pagarme/PagarMeAPI.php';
require_once __DIR__ . '/../../src/integrations/payments/stripe/StripeAPI.php';

// Inicializa a conexão com o banco de dados
$db = new Database();

// Verifica autenticação
$auth = new Authentication($db);
if (!$auth->isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Obtém o usuário atual
$currentUser = $auth->getCurrentUser();

// Inicializa o controlador de pedidos
$orderController = new OrderController($db);

// Define o cabeçalho de resposta como JSON
header('Content-Type: application/json');

// Processa a requisição
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            // Endpoints GET
            switch ($action) {
                case 'list_payment_methods':
                    // Lista os métodos de pagamento disponíveis
                    echo json_encode([
                        'payment_methods' => [
                            'credit_card' => 'Cartão de Crédito',
                            'boleto' => 'Boleto Bancário',
                            'stripe' => 'Stripe'
                        ]
                    ]);
                    break;
                    
                default:
                    throw new Exception('Ação não suportada');
            }
            break;
            
        case 'POST':
            // Endpoints POST
            $data = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'process_payment':
                    // Processa um pagamento
                    if (!isset($data['payment_method'])) {
                        throw new Exception('Método de pagamento não informado');
                    }
                    
                    // Validação de dados específicos para cada método de pagamento
                    switch ($data['payment_method']) {
                        case 'credit_card':
                            if (!isset($data['card']) || !isset($data['card']['number']) || !isset($data['card']['holder_name']) || 
                                !isset($data['card']['expiration_date']) || !isset($data['card']['cvv'])) {
                                throw new Exception('Dados do cartão incompletos');
                            }
                            break;
                            
                        case 'boleto':
                            if (!isset($data['customer']) || !isset($data['customer']['name']) || !isset($data['customer']['document'])) {
                                throw new Exception('Dados do cliente incompletos');
                            }
                            break;
                            
                        case 'stripe':
                            if (!isset($data['token'])) {
                                throw new Exception('Token do Stripe não informado');
                            }
                            break;
                            
                        default:
                            throw new Exception('Método de pagamento não suportado');
                    }
                    
                    // Cria o pedido e processa o pagamento
                    $result = $orderController->createOrder($currentUser['id'], $data);
                    
                    echo json_encode($result);
                    break;
                    
                case 'refund_payment':
                    // Estorna um pagamento
                    if (!isset($data['order_id'])) {
                        throw new Exception('ID do pedido não informado');
                    }
                    
                    $result = $orderController->cancelOrder($data['order_id'], $currentUser['id']);
                    
                    echo json_encode($result);
                    break;
                    
                default:
                    throw new Exception('Ação não suportada');
            }
            break;
            
        default:
            throw new Exception('Método não suportado');
    }
} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => $e->getMessage()]);
} 