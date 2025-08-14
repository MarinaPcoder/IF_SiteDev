<?php

    namespace App\Models;

    use App\Controllers\JogoController;
    use App\Core\DB\Conexao;
    use PDO;
    use PDOException;

    final class JogoCRUD {
        
        public function Create(JogoController $jogo) {
            $comando = "
                INSERT INTO jogo (
                    titulo, 
                    plataforma, 
                    data_lancamento, 
                    desenvolvedora,  
                    link_compra,
                    descricao
                ) VALUES (
                    :titulo,
                    :plataforma,
                    :data,
                    :desenvolvedora,
                    :link,
                    :descricao
                );
                
                INSERT INTO jogo_genero (
                    id_genero,
                    id_jogo
                ) VALUES (
                    :id_genero,
                    (SELECT id FROM jogo WHERE titulo = :titulo AND plataforma = :plataforma)
                );
            ";

            $stmt = Conexao::getInstancia()->prepare(query: $comando);

            $stmt -> bindValue(param: ":titulo",            value: $jogo->GetTitulo(),          type: PDO::PARAM_STR);
            $stmt -> bindValue(param: ":plataforma",        value: $jogo->GetPlataforma(),      type: PDO::PARAM_STR);
            $stmt -> bindValue(param: ":data",              value: $jogo->GetDataLancamento(),  type: PDO::PARAM_STR);
            $stmt -> bindValue(param: ":desenvolvedora",    value: $jogo->GetDesenvolvedora(),  type: PDO::PARAM_STR);
            $stmt -> bindValue(param: ":link",              value: $jogo->GetLink(),            type: PDO::PARAM_STR);
            $stmt -> bindValue(param: ":descricao",         value: $jogo->GetDescricao(),       type: PDO::PARAM_STR);
            $stmt -> bindValue(param: ":id_genero",         value: $jogo->GetGenero(),          type: PDO::PARAM_STR);

            // Executa e verifica
            $success = $stmt->execute();

            if (! $success) {
                // Pega informação de erro do driver
                $errorInfo = $stmt->errorInfo();
                throw new PDOException(
                    message: "Erro ao inserir jogo: " .
                    ($errorInfo[2] ?? 'Desconhecido')
                );
            }

            return true;
        }

        public function Read($id) {
            $comando = "
                SELECT * FROM jogo WHERE id_jogo = '$id'
            ";
            
            $stmt = Conexao::getInstancia()->prepare(query: $comando);

            // Executa e verifica
            $success = $stmt->execute();

            if (! $success) {
                // Pega informação de erro do driver
                $errorInfo = $stmt->errorInfo();
                throw new PDOException(
                    message: "Erro ao ler jogo: " .
                    ($errorInfo[2] ?? 'Desconhecido')
                );
            }
        }

        public function Update(JogoController $jogo) {
            $comando = "
                UPDATE jogo
                
                SET 
                    titulo = :titulo,
                    plataforma = :plataforma, 
                    data_lancamento = :data,
                    desenvolvedora = :desenvolvedora,  
                    link_compra = :link,
                    descricao = :descricao
                    
                    WHERE id_jogo = '".$jogo->GetId()."'
            ";

            $stmt = Conexao::getInstancia()->prepare(query: $comando);

            // Executa e verifica
            $success = $stmt->execute();

            if (! $success) {
                // Pega informação de erro do driver
                $errorInfo = $stmt->errorInfo();
                throw new PDOException(
                    message: "Erro ao editar jogo: " .
                    ($errorInfo[2] ?? 'Desconhecido')
                );
            }
        }

        public function Delete($id) {
            $comando = 
            "
                DELETE FROM jogo 
                
                WHERE   
                    id_jogo = $id
            ";

            $stmt = Conexao::getInstancia()->prepare(query: $comando);

            // Executa e verifica
            $success = $stmt->execute();

            if (! $success) {
                // Pega informação de erro do driver
                $errorInfo = $stmt->errorInfo();
                throw new PDOException(
                    message: "Erro ao deletar jogo: " .
                    ($errorInfo[2] ?? 'Desconhecido')
                );
            }
        }
    }
    