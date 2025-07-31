<?php
 
 namespace App\Models;

use App\Core\DB\Conexao;
use PDO;
use PDOException;

Class UsuarioCRUD {
    

    public function Create($usuario) {

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
        $stmt = Conexao::getInstancia()->prepare(query: $comando);

        $stmt->bindValue(param: ':nome',            value: $usuario->getNome(),                                type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':email',           value: $usuario->getEmail(),                               type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':senha',           value: $usuario->getSenhaCrip(),                           type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':data_nascimento', value: $usuario->getDataNascimento(),                      type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':bio',             value: $usuario->getBio(),                                 type: PDO::PARAM_STR);

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
            SELECT * FROM usuario WHERE id_usuario = '$id'
        ";

        $stmt = Conexao::getInstancia()->prepare(query: $comando);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);

            return $resultado;
            
        } else {
            return [];
        }
    }

    public function Update($usuario) {
        $comando = "
            UPDATE usuario 
            
            SET 
                nome_usuario = :nome, 
                email = :email, 
                senha = :senha, 
                data_nascimento = :data_nascimento,
                bio = :bio
                
                WHERE id = '$usuario->get'
        ";

        $stmt = Conexao::getInstancia()->prepare(query: $comando);

        $stmt->bindValue(param: ':nome',            value: $usuario->getNome(),                                type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':email',           value: $usuario->getEmail(),                               type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':senha',           value: $usuario->getSenhaCrip(),                           type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':data_nascimento', value: $usuario->getDataNascimento(),                      type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':bio',             value: $usuario->getBio(),                                 type: PDO::PARAM_STR);


    }

    public function Delete($id) {
        
    }

    public function GetId($email): mixed{
        $comando = "
            SELECT id_usuario FROM usuario WHERE email = '$email'
        ";

        $stmt = Conexao::getInstancia()->prepare(query: $comando);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);

            return $resultado[0]['id_usuario'];
            
        } else {
            return [];
        }
    }


    public function Getsenha($email): array {
        // Função teste (redudante já que temos a função READ)

        $comando = "
            SELECT senha FROM usuario WHERE email  = '$email'
        ";

        $stmt = Conexao::getInstancia()->prepare(query: $comando);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $senha = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);

            return $senha;
            
        } else {
            return [];
        }

    }
    
}