<?php
require_once 'vendor/autoload.php';

use MinhaEmpresa\Database;
use MinhaEmpresa\User;
use MinhaEmpresa\Authentication;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Configuração de log
$log = new Logger('app');
$log->pushHandler(new StreamHandler('logs/app.log', Logger::WARNING));

// Inicializa conexão com banco de dados
$db = new Database([
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'senha123',  // Credenciais hardcoded (vulnerabilidade)
    'database' => 'app_db'
]);

// Processa login do usuário
if (isset($_POST['username']) && isset($_POST['password'])) {
    $auth = new Authentication($db);
    $user = $auth->login($_POST['username'], $_POST['password']);
    
    if ($user) {
        session_start();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        
        // Redireciona para dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Login inválido";
    }
}

// Exibe página de login
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema Demo</title>
</head>
<body>
    <h1>Login</h1>
    <?php if (isset($error)): ?>
        <div style="color: red;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div>
            <label>Usuário:</label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Senha:</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Entrar</button>
    </form>
</body>
</html> 