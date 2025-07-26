<?php
 
 namespace App\Models;

use App\Core\DB\Conexao;
use PDO;
use PDOException;

Class UsuarioCRUD {
    

    public function Create($usuario) {
        
        // Timestamp de hoje (se precisar)
        $criacao = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        $comando = "
            INSERT INTO usuario (
                nome_usuario, 
                email, 
                senha, 
                data_nascimento, 
                tipo_perfil, 
                criado_em, 
                pontos_gamificacao, 
                status_ativo, 
                bio
            ) VALUES (
                :nome,
                :email,
                :senha,
                :data_nascimento,
                :tipo_perfil,
                :criado_em,
                :pontos,
                :status,
                :bio
            )
        ";
        $stmt = Conexao::getInstancia()->prepare(query: $comando);

        $stmt->bindValue(param: ':nome',            value: $usuario->getNome(),              type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':email',           value: $usuario->getEmail(),             type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':senha',           value: $usuario->getSenhaCrip(),         type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':data_nascimento', value: $usuario->getDataNascimento(),    type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':tipo_perfil',     value: 'usuario',                        type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':criado_em', value: (new \DateTime())->format(format: 'Y-m-d H:i:s'),type: PDO::PARAM_STR);
        $stmt->bindValue(param: ':pontos',          value: 0,                                type: PDO::PARAM_INT);
        $stmt->bindValue(param: ':status',          value: True,                             type: PDO::PARAM_BOOL);
        $stmt->bindValue(param: ':bio',             value: $usuario->getBio(),               type: PDO::PARAM_STR);


        // Executa e verifica
        $success = $stmt->execute();
        if (! $success) {
            // Pega informação de erro do driver
            $errorInfo = $stmt->errorInfo();
            throw new PDOException(
                "Erro ao inserir usuário: " .
                ($errorInfo[2] ?? 'Desconhecido')
            );
        }

        return true;
    }
}