<?php

    namespace App\Models;

    use App\Controllers\JogoController;
    use App\Core\DB\Conexao;
    use PDO;
    use PDOException;

    class JogoCRUD {

    private PDO $pdo;

        public function __construct()
        {
            $this->pdo = Conexao::getInstancia();
            $this->pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
        }

        public function create(JogoController $jogo): bool
        {
            $this->pdo = Conexao::getInstancia();
            try {
                $this->pdo->beginTransaction();

                $sqlJogo = "
                    INSERT INTO jogo (
                        titulo, plataforma, data_lancamento, desenvolvedora, link_compra, descricao
                    ) VALUES (
                        :titulo, :plataforma, :data, :desenvolvedora, :link, :descricao
                    )
                ";
                $stmtJogo = $this->pdo->prepare(query: $sqlJogo);
                $stmtJogo->bindValue(param: ':titulo',         value: $jogo->getTitulo(),         type: PDO::PARAM_STR);
                $stmtJogo->bindValue(param: ':plataforma',     value: $jogo->getPlataforma(),     type: PDO::PARAM_STR);
                $stmtJogo->bindValue(param: ':data',           value: $jogo->getDataLancamento(), type: PDO::PARAM_STR);
                $stmtJogo->bindValue(param: ':desenvolvedora', value: $jogo->getDesenvolvedora(), type: PDO::PARAM_STR);
                $stmtJogo->bindValue(param: ':link',           value: $jogo->getLink(),           type: PDO::PARAM_STR);
                $stmtJogo->bindValue(param: ':descricao',      value: $jogo->getDescricao(),      type: PDO::PARAM_STR);
                $stmtJogo->execute();

                // ultimo id inserido no BD
                $idJogo = (int) $this->pdo->lastInsertId();

                // Insere na tabela jogo_genero
                $sqlJG = "INSERT INTO Jogo_Genero (id_jogo, id_genero) VALUES (:id_jogo, :id_genero)";
                $stmtJG = $this->pdo->prepare(query: $sqlJG);
                $stmtJG->bindValue(param: ':id_jogo',   value: $idJogo,              type: PDO::PARAM_INT);
                $stmtJG->bindValue(param: ':id_genero', value: $jogo->GetGenero(),  type: PDO::PARAM_INT);
                $stmtJG->execute();

                $this->pdo->commit();
                
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw new PDOException(message: "Falha ao criar jogo/relacionar gênero: " . $e->getMessage(), code: 0, previous: $e);
            }

            return True;
        }


        public function Read($idJogo) {

            $this->pdo = Conexao::getInstancia();

            try {
                $this->pdo->beginTransaction();

                $sqlJogo = "
                SELECT * FROM jogo WHERE id_jogo = :id
                ";

                $stmtJogo = $this->pdo->prepare(query: $sqlJogo);
                $stmtJogo -> bindValue(param: ':id', value: $idJogo, type: PDO::PARAM_INT );
                $stmtJogo->execute();
                $jogo = $stmtJogo->fetch(PDO::FETCH_ASSOC);

                if (!$jogo) { $this->pdo->rollBack(); return false; }

                // Ler na tabela jogo_genero
                $sqlJG = "SELECT id_genero FROM jogo_genero WHERE id_jogo = :id";
                $stmtJG = $this->pdo->prepare(query: $sqlJG);
                $stmtJG->bindValue(param: ':id',   value: $idJogo, type: PDO::PARAM_INT);
                $stmtJG->execute();

                $genero = $stmtJG->fetch(PDO::FETCH_ASSOC);
                if (!$genero) {
                    $this->pdo->rollBack(); 
                    return false; 
                }

                $jogo['generos'] = $genero;

                $this->pdo->commit();
                

            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                throw new PDOException(message: "Falha ao ler jogo: ".$e->getMessage(), code: 0, previous: $e);
            }

            return $jogo;
        }

        public function Update(JogoController $jogo) {
            
            $this->pdo = Conexao::getInstancia();

            try {
                $this->pdo->beginTransaction();

                $sqlJogo = "
                UPDATE jogo 
                
                SET
                    titulo = :titulo,
                    descricao = :descricao,
                    desenvolvedora = :desenvolvedora,
                    data_lancamento = :data,
                    link_compra = :link,
                    plataforma = :plataforma

                    WHERE id_jogo = :id
                ";

                $stmtJogo = $this->pdo->prepare(query: $sqlJogo);
                $stmtJogo -> bindValue(param: ":titulo",            value: $jogo->GetTitulo(),              type: PDO::PARAM_STR);
                $stmtJogo -> bindValue(param: ":descricao",         value: $jogo->GetDescricao(),           type: PDO::PARAM_STR);
                $stmtJogo -> bindValue(param: ":desenvolvedora",    value: $jogo->GetDesenvolvedora(),      type: PDO::PARAM_STR);
                $stmtJogo -> bindValue(param: ":data",              value: $jogo->GetDataLancamento(),      type: PDO::PARAM_STR);
                $stmtJogo -> bindValue(param: ":link",              value: $jogo->GetLink(),                type: PDO::PARAM_STR);
                $stmtJogo -> bindValue(param: ":plataforma",        value: $jogo->GetPlataforma(),          type: PDO::PARAM_STR);
                $stmtJogo -> bindValue(param: ":id",                value: $jogo->GetId(),                  type: PDO::PARAM_INT);
                $stmtJogo->execute();

                $sqlJG = "
                UPDATE jogo_genero

                SET id_genero = :id_genero

                WHERE id_jogo = :id_jogo
                ";

                $stmtJG = $this->pdo->prepare(query: $sqlJG);
                $stmtJG -> bindValue(param: ':id_genero',   value: $jogo->GetGenero(),  type: PDO::PARAM_INT);
                $stmtJG -> bindValue(param: ':id_jogo',     value: $jogo->GetId(),      type: PDO::PARAM_INT);
                $stmtJG ->execute();

                $this -> pdo -> commit();

            }  catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw new PDOException(message: "Falha ao editar jogo: " . $e->getMessage(), code: 0, previous: $e);
            }

            return True;
        }

        public function Delete($id) {

            $this->pdo = Conexao::getInstancia();

            try {
                $this->pdo->beginTransaction();

                $sqlJG = "
                DELETE FROM jogo_genero

                WHERE
                    id_jogo = :id
                ";

                $stmtJG = $this->pdo->prepare(query: $sqlJG);
                $stmtJG -> bindValue(param: ":id", value: $id, type: PDO::PARAM_INT);
                $stmtJG -> execute();

                $sqlJogo = "
                DELETE FROM jogo 
                
                WHERE   
                    id_jogo = :id
                ";

                $stmtJogo = $this -> pdo -> prepare(query: $sqlJogo);
                $stmtJogo -> bindValue(param: ":id", value: $id, type: PDO::PARAM_INT);
                $stmtJogo->execute();

                $this -> pdo -> commit();

            }  catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw new PDOException(message: "Falha ao deletar jogo: " . $e->getMessage(), code: 0, previous: $e);
            }

            return True;
        }
    }
    