<?php
/**
 * Configuração do Banco de Dados usando variáveis de ambiente
 */

// Carregar o autoload do Composer
require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Função para obter variável de ambiente com fallback
function env($key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);
    return $value !== false ? $value : $default;
}

// Configuração do banco de dados a partir das variáveis de ambiente
$host = env('DB_HOST', 'localhost');
$dbname = env('DB_NAME', 'hgsa7692_utm');
$user = env('DB_USER', 'root');
$password = env('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Em produção, não exiba detalhes do erro
    if (env('APP_DEBUG', false)) {
        die("Erro na conexão: " . $e->getMessage());
    } else {
        die("Erro ao conectar ao banco de dados. Contate o administrador.");
    }
}