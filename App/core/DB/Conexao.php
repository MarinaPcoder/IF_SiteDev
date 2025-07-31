<?php 

namespace App\Core\DB;

use PDO;
use PDOException;

class Conexao {

    private static $instancia = null;
    private $pdo;

    private function __construct()
    {
        $host = 'localhost';
        $dbName = 'storm';
        $username = 'root';
        $porta = '3306';
        $pass = '';
        $dsn = "mysql:host=$host; port=$porta; dbname=$dbName;";
        
        try {
            
            $this->pdo = new PDO(dsn: $dsn, username: $username, password: $pass);
        } catch (PDOException $e) {
            die('Erro na conexão: ' . $e->getMessage());
        }
    }

    public static function getInstancia(): PDO
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }

        return self::$instancia->pdo;
    }
}
$pdo = Conexao::getInstancia();