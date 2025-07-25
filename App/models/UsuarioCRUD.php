<?php
 
 namespace App\Models;

use App\Core\DB\Conexao;
Use PDO;

Class UsuarioCRUD {
    public function Create($usuario) {
        
        $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        $comando = "INSERT INTO usuario (nome_usuario, email, senha, data_nascimento, tipo_perfil, criado_em, pontos_gamificacao, status_ativo, bio) VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = Conexao::getInstancia()->prepare(query: $comando);
        $stmt->bindValue(param: 1, value: $usuario->getNome(), type: PDO::PARAM_STR);
        $stmt->bindValue(param: 2, value: $usuario->getEmail(), type: PDO::PARAM_STR);
        $stmt->bindValue(param: 3, value: $usuario->getSenha(), type: PDO::PARAM_STR);
        $stmt->bindValue(param: 4, value: $usuario->getDataNascimento(), type: PDO::PARAM_STR);
        $stmt->bindValue(param: 5, value: True, type: PDO::PARAM_STR);
        $stmt->bindValue(param: 6, value: $hoje, type: PDO::PARAM_STR);
        $stmt->bindValue(param: 7, value: 0, type: PDO::PARAM_STR);
        $stmt->bindValue(param: 8, value: 1, type: PDO::PARAM_STR);
        $stmt->bindValue(param: 9, value: $usuario->GetBio(), type: PDO::PARAM_STR);


        $stmt->execute();
    }
}