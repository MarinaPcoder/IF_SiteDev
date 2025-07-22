<?php 

namespace App\Model;

use PDO;
use PDOException;

class Conexao {

    private static $instancia = null;
    private $pdo;

    private function __construct()
    {
        $host = '127.0.0.1';
        $dbName = 'storm';
        $username = 'root';
        $pass = '';
        $dsn = "mysql:host=$host;dbname=$dbName;";
        
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