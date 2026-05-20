<?php
/**
 * Configuração do Banco de Dados - Singleton Pattern
 * Usa variáveis de ambiente via dotenv
 * Versão: 2.2
 */

// Carregar o autoload do Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Função para obter variável de ambiente com fallback
if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value !== false ? $value : $default;
    }
}

/**
 * Singleton Database Connection
 * Centraliza a conexão PDO para evitar múltiplas instâncias
 */
class Database
{
    private static $instance = null;

    /**
     * Retorna a instância única da conexão PDO
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            $host = env('DB_HOST', 'localhost');
            $dbname = env('DB_NAME', 'hgsa7692_utm');
            $user = env('DB_USER', 'root');
            $password = env('DB_PASS', '');

            try {
                self::$instance = new PDO(
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
                if (env('APP_DEBUG', false)) {
                    die("Erro na conexão: " . $e->getMessage());
                } else {
                    die("Erro ao conectar ao banco de dados. Contate o administrador.");
                }
            }
        }
        return self::$instance;
    }

    // Prevenir clonagem e desserialização
    private function __construct()
    {
    }
    private function __clone()
    {
    }
    public function __wakeup()
    {
        throw new \Exception("Singleton não pode ser desserializado");
    }
}

// Compatibilidade retroativa: $pdo disponível globalmente
$pdo = Database::getInstance();