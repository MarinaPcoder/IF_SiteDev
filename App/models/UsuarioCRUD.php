<?php
 
 namespace App\Models;

use App\Controllers\UsuarioController;
use App\Core\DB\Conexao;
use PDO;
use PDOException;

Class UsuarioCRUD {

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstancia();
        $this->pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
    }
    public function Create(UsuarioController $usuario) {

        $comando = "
            INSERT INTO usuario (
                nome_usuario, 
                email, 
                senha, 
                data_nascimento,  
                bio
            ) VALUES (
                :nome,
                :email,
                :senha,
                :data_nascimento,
                :bio
            )
        ";
        
        $stmt = $this->pdo->prepare(query: $comando);

        $stmt->bindValue(param: ':nome',            value: $usuario->getNome(),             type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':email',           value: $usuario->getEmail(),            type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':senha',           value: $usuario->getSenhaCrip(),        type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':data_nascimento', value: $usuario->getDataNascimento(),   type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':bio',             value: $usuario->getBio(),              type: PDO::PARAM_STR);

        // Executa e verifica
        $success = $stmt->execute();
        if (! $success) {
            // Pega informação de erro do driver
            $errorInfo = $stmt->errorInfo();
            throw new PDOException(
                message: "Erro ao inserir usuário: " .
                ($errorInfo[2] ?? 'Desconhecido')
            );
        }

        return true;
    }

    public function Read($id): array {
        $comando = "
            SELECT * FROM usuario WHERE id_usuario = :id
        ";

        $stmt = $this->pdo->prepare(query: $comando);
        $stmt->bindValue(param: ':id', value: $id, type: PDO::PARAM_INT);
        $success = $stmt->execute();

        if (!$success) {
            // Pega informação de erro do driver
            $errorInfo = $stmt->errorInfo();
            throw new PDOException(
                message: "Erro ao ler usuário: " .
                ($errorInfo[2] ?? 'Desconhecido')
            );
        }

        if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);

            return $resultado;
            
        } else {
            return [];
        }
    }

    public function Update(UsuarioController $usuario) {
        $comando = "
            UPDATE usuario 
            
            SET 
                nome_usuario = :nome, 
                email = :email, 
                senha = :senha, 
                data_nascimento = :data_nascimento,
                bio = :bio
                
                WHERE id_usuario = :id
        ";

        $stmt = $this->pdo->prepare(query: $comando);

        $stmt->bindValue(param: ':nome',            value: $usuario->getNome(),             type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':email',           value: $usuario->getEmail(),            type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':senha',           value: $usuario->getSenhaCrip(),        type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':data_nascimento', value: $usuario->getDataNascimento(),   type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':bio',             value: $usuario->getBio(),              type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':id',              value: $usuario->GetId(),               type: PDO::PARAM_INT);

        // Executa e verifica
        $sucesso = $stmt->execute();
        if (! $sucesso) {
            // Pega informação de erro do driver
            $errorInfo = $stmt->errorInfo();
            throw new PDOException(
                message: "Erro ao editar o usuário: " .
                ($errorInfo[2] ?? 'Desconhecido')
            );
        }

        return true;
    }

    public function Delete($id): bool {
        $comando = 
        "
            DELETE FROM usuario 
            
            WHERE   
                id_usuario = :id
        ";

        $stmt = $this->pdo->prepare(query: $comando);
        $stmt -> bindValue(param: ':id', value: $id, type: PDO::PARAM_INT);
        $sucesso = $stmt->execute();
        
        if (!$sucesso) {
            // Pega informação de erro do driver
            $errorInfo = $stmt->errorInfo();
            throw new PDOException(
                message: "Erro ao deletar o usuário: " .
                ($errorInfo[2] ?? 'Desconhecido')
            );
        }

        return True;
    }

    public function GetId($email): mixed{
        $comando = "
            SELECT id_usuario FROM usuario WHERE email = :email
        ";

        $stmt = $this->pdo->prepare(query: $comando);
        $stmt -> bindValue(param: ':email', value: $email, type: PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);

            return $resultado[0]['id_usuario'];
            
        } else {
            return [];
        }
    }

}