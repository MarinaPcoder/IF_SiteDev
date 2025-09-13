<?php

    namespace App\Models;

    use App\Controllers\AvaliacaoController;
    use App\Core\DB\Conexao;
    use PDO;
    use PDOException;

    class AvaliacaoCRUD {
    
    private PDO $pdo;

        public function __construct()
        {
            $this->pdo = Conexao::getInstancia();
            $this->pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
        }

        public function Create(AvaliacaoController $avaliacao) {
            try {
                $this -> pdo -> beginTransaction();

                $sqlCreate = "
                    INSERT INTO avaliacao (
                        id_usuario, id_jogo, justificativa, nota
                    ) VALUES (
                        :usuario, :jogo, :justificativa, :nota
                    )";

                $stmtJogo = $this->pdo->prepare(query: $sqlCreate); 
                $stmtJogo->bindValue(param: ':usuario',       value: $avaliacao->getIdUsuario(),     type: PDO::PARAM_INT);
                $stmtJogo->bindValue(param: ':jogo',          value: $avaliacao->getIdJogo(),        type: PDO::PARAM_INT);
                $stmtJogo->bindValue(param: ':justificativa', value: $avaliacao->getJustificativa(), type: PDO::PARAM_STR);
                $stmtJogo->bindValue(param: ':nota',          value: $avaliacao->getNota(),          type: PDO::PARAM_INT);
                $sucesso = $stmtJogo->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new \Exception("Erro ao cadastrar avaliação", 1);
                }

                $this->pdo->commit();

            } catch (PDOException $pe) {
                $this->pdo->rollBack();
                throw new \Exception("Erro ao processar solicitação: " . $pe->getMessage(), $pe->getCode(), $pe);
            }
        }

        public function Read($id): array {
            try {
                $sqlRead = "SELECT * FROM avaliacao WHERE id_avaliacao = :id";
                $stmt = $this->pdo->prepare(query: $sqlRead);
                $stmt->bindValue(param: ':id', value: $id, type: PDO::PARAM_INT);
                $stmt->execute();
                $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $resultado;

            } catch (PDOException $pe) {
                throw new \Exception("Erro ao processar solicitação: " . $pe->getMessage(), $pe->getCode(), $pe);
            }
        }

        public function Update(AvaliacaoController $avaliacao) {
            try {

                $this->pdo->beginTransaction();

                $sqlUpdate = "
                    UPDATE avaliacao SET
                        justificativa = :justificativa,
                        nota = :nota
                    WHERE id_avaliacao = :id";

                $stmt = $this->pdo->prepare(query: $sqlUpdate);
                $stmt->bindValue(param: ':justificativa', value: $avaliacao->GetJustificativa(), type: PDO::PARAM_STR);
                $stmt->bindValue(param: ':nota', value: $avaliacao->GetNota(), type: PDO::PARAM_INT);
                $stmt->bindValue(param: ':id', value: $avaliacao->GetId(), type: PDO::PARAM_INT);
                $sucesso = $stmt->execute();

                if ($sucesso) {
                    $this->pdo->commit();
                    return true;
                } else {
                    $this->pdo->rollBack();
                    return false;
                }

            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                throw new \Exception("Erro ao processar solicitação: " . $e->getMessage(), $e->getCode(), $e);
            }
        }

        public function Delete($id) {
            try {
                $this->pdo->beginTransaction();

                $sqlDelete = "DELETE FROM avaliacao WHERE id_avaliacao = :id";
                $stmt = $this->pdo->prepare(query: $sqlDelete);
                $stmt->bindValue(param: ':id', value: $id, type: PDO::PARAM_INT);
                $sucesso = $stmt->execute();

                if ($sucesso) {
                    $this->pdo->commit();
                    return true;
                } else {
                    $this->pdo->rollBack();
                    return false;
                }

            } catch (PDOException $pe) {
                $this->pdo->rollBack();
                throw new \Exception("Erro ao processar solicitação: " . $pe->getMessage(), $pe->getCode(), $pe);
            }
        }

        public function ReadByUserAndGame($id_usuario, $id_jogo): array {  
            try {
                $sqlRead = "SELECT * FROM avaliacao WHERE id_usuario = :usuario AND id_jogo = :jogo";
                $stmt = $this->pdo->prepare(query: $sqlRead);
                $stmt->bindValue(param: ':usuario', value: $id_usuario, type: PDO::PARAM_INT);
                $stmt->bindValue(param: ':jogo', value: $id_jogo, type: PDO::PARAM_INT);
                $stmt->execute();
                $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $resultado;

            } catch (PDOException $pe) {
                throw new \Exception("Erro ao processar solicitação: " . $pe->getMessage(), $pe->getCode(), $pe);
            }
        }
    }