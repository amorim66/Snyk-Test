<?php
require_once 'vendor/autoload.php';

use MinhaEmpresa\Database;
use MinhaEmpresa\User;
use MinhaEmpresa\Authentication;
use MinhaEmpresa\Security\InputValidator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Carrega variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuração de log
$log = new Logger('app');
$logFile = 'logs/app.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$log->pushHandler(new StreamHandler($logFile, Logger::WARNING));

// Inicializa conexão com banco de dados usando variáveis de ambiente
$db = new Database([
    'host' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'database' => getenv('DB_NAME') ?: 'app_db'
]);

// Processa login do usuário
if (isset($_POST['username']) && isset($_POST['password'])) {
    try {
        // Validação e sanitização de entrada
        $username = InputValidator::sanitizeString($_POST['username']);
        $password = $_POST['password']; // Não sanitizar senha bruta
        
        $auth = new Authentication($db);
        $user = $auth->login($username, $password);
        
        if ($user) {
            // Inicia sessão com configurações seguras
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.use_only_cookies', 1);
            session_start();
            
            // Regenera ID da sessão para prevenir fixação de sessão
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();
            
            // Registra log de login bem-sucedido
            $log->info('Login bem-sucedido para usuário: ' . $username);
            
            // Redireciona para dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Usuário ou senha inválidos";
            $log->warning('Tentativa de login falhou para usuário: ' . $username);
        }
    } catch (Exception $e) {
        $error = "Erro ao processar login";
        $log->error('Erro no login: ' . $e->getMessage());
    }
}

// Proteção contra CSRF
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

// Exibe página de login com proteção XSS
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema Demo</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>Login</h1>
    <?php if (isset($error)): ?>
        <div style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <div>
            <label for="username">Usuário:</label>
            <input type="text" id="username" name="username" required autocomplete="username">
        </div>
        <div>
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit">Entrar</button>
    </form>
</body>
</html> 