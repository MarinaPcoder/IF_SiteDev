<?php

    namespace App\Models;

    use App\Controllers\JogoController;
    use App\Core\DB\Conexao;
    use PDO;
    use PDOException;

    class JogoCRUD {
        
public function create(JogoController $jogo): int
{
    $pdo = Conexao::getInstancia();
    try {
        $pdo->beginTransaction();

        // 1) Insere o jogo
        $sqlJogo = "
            INSERT INTO Jogo (
                titulo, plataforma, data_lancamento, desenvolvedora, link_compra, descricao
            ) VALUES (
                :titulo, :plataforma, :data, :desenvolvedora, :link, :descricao
            )
        ";
        $stmtJogo = $pdo->prepare(query: $sqlJogo);
        $stmtJogo->bindValue(param: ':titulo',         value: $jogo->getTitulo(),         type: PDO::PARAM_STR);
        $stmtJogo->bindValue(param: ':plataforma',     value: $jogo->getPlataforma(),     type: PDO::PARAM_STR);
        $stmtJogo->bindValue(param: ':data',           value: $jogo->getDataLancamento(), type: PDO::PARAM_STR);
        $stmtJogo->bindValue(param: ':desenvolvedora', value: $jogo->getDesenvolvedora(), type: PDO::PARAM_STR);
        $stmtJogo->bindValue(param: ':link',           value: $jogo->getLink(),           type: PDO::PARAM_STR);
        $stmtJogo->bindValue(param: ':descricao',      value: $jogo->getDescricao(),      type: PDO::PARAM_STR);
        $stmtJogo->execute();

        // ultimo id inserido no BD
        $idJogo = (int)$pdo->lastInsertId();

        // Insere na tabela 
        $sqlJG = "INSERT INTO Jogo_Genero (id_jogo, id_genero) VALUES (:id_jogo, :id_genero)";
        $stmtJG = $pdo->prepare(query: $sqlJG);
        $stmtJG->bindValue(param: ':id_jogo',   value: $idJogo,              type: PDO::PARAM_INT);
        $stmtJG->bindValue(param: ':id_genero', value: $jogo->GetGenero(),  type: PDO::PARAM_INT);
        $stmtJG->execute();

        $pdo->commit();
        
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new PDOException(message: "Falha ao criar jogo/relacionar gênero: " . $e->getMessage(), code: 0, previous: $e);
    }

    return True;
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

            if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);

            return $resultado;
            
            } else {
                return [];
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
    