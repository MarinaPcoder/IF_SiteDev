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

                // Define Logo Padrão
                $sqlLogo = "
                    INSERT INTO jogo_imagem (
                        id_jogo, 
                        caminho,
                        tipo,
                        ordem_exib
                    ) 
                    VALUES (
                        :id_jogo, 
                        :caminho,
                        :tipo,
                        :ordem_exib
                    )
                ";

                $stmtLogo = $this->pdo->prepare(query: $sqlLogo);
                $stmtLogo->bindValue(param: ':id_jogo', value: $idJogo, type: PDO::PARAM_INT);
                $stmtLogo->bindValue(param: ':caminho', value: 'img/logo.png', type: PDO::PARAM_STR);
                $stmtLogo->bindValue(param: ':tipo', value: 'logo', type: PDO::PARAM_STR);
                $stmtLogo->bindValue(param: ':ordem_exib', value: 0, type: PDO::PARAM_INT);
                $stmtLogo->execute();

                // Define Banner Padrão
                $sqlBanner = "
                    INSERT INTO jogo_imagem (
                        id_jogo, 
                        caminho,
                        tipo,
                        ordem_exib
                    ) 
                    VALUES (
                        :id_jogo, 
                        :caminho,
                        :tipo,
                        :ordem_exib
                    )
                ";

                $stmtBanner = $this->pdo->prepare(query: $sqlBanner);
                $stmtBanner->bindValue(param: ':id_jogo', value: $idJogo, type: PDO::PARAM_INT);
                $stmtBanner->bindValue(param: ':caminho', value: 'img/banner.png', type: PDO::PARAM_STR);
                $stmtBanner->bindValue(param: ':tipo', value: 'banner', type: PDO::PARAM_STR);
                $stmtBanner->bindValue(param: ':ordem_exib', value: 0, type: PDO::PARAM_INT);
                $stmtBanner->execute();

                $this->pdo->commit();
                
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw new PDOException(message: "Falha ao criar jogo/relacionar gênero: " . $e->getMessage(), code: 0, previous: $e);
            }

            return True;
        }


        public function Read($idJogo): mixed {

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

                $sqlLogo = "
                SELECT caminho 
                FROM jogo_imagem 
                WHERE id_jogo = :id AND tipo = 'logo'
                ";
                $stmtLogo = $this->pdo->prepare(query: $sqlLogo);
                $stmtLogo->bindValue(param: ':id', value: $idJogo, type: PDO::PARAM_INT);
                $stmtLogo->execute();
                $jogo['logo'] = $stmtLogo->fetchColumn();

                $sqlBanner = "
                SELECT caminho 
                FROM jogo_imagem 
                WHERE id_jogo = :id AND tipo = 'banner'
                ";

                $stmtBanner = $this->pdo->prepare(query: $sqlBanner);
                $stmtBanner->bindValue(param: ':id', value: $idJogo, type: PDO::PARAM_INT);
                $stmtBanner->execute();
                $jogo['banner'] = $stmtBanner->fetchColumn();

                $stmtScreenshot = $this->pdo->prepare(query: "
                SELECT caminho 
                FROM jogo_imagem 
                WHERE id_jogo = :id AND tipo = 'screenshot'
                ");
                $stmtScreenshot->bindValue(param: ':id', value: $idJogo, type: PDO::PARAM_INT);
                $stmtScreenshot->execute();
                $jogo['screenshots'] = $stmtScreenshot->fetchAll(PDO::FETCH_COLUMN);

                $this->pdo->commit();
            
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                throw new PDOException(message: "Falha ao ler jogo: ".$e->getMessage(), code: 0, previous: $e);
            }

            return $jogo;
        }

        public function Update(JogoController $jogo): bool{
            
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

        public function ReadByTitleAndPlatform($titulo, $plataforma) {


            try {
                $this->pdo->beginTransaction();

                $sqlJogo = "
                SELECT * FROM jogo WHERE titulo = :titulo AND plataforma = :plataforma
                ";

                $stmtJogo = $this->pdo->prepare(query: $sqlJogo);
                $stmtJogo -> bindValue(param: ':titulo', value: $titulo, type: PDO::PARAM_STR );
                $stmtJogo -> bindValue(param: ':plataforma', value: $plataforma, type: PDO::PARAM_STR );
                $stmtJogo->execute();
                $jogo = $stmtJogo->fetch(PDO::FETCH_ASSOC);

                if (!$jogo) { $this->pdo->rollBack(); return false; }

                // Ler na tabela jogo_genero
                $sqlJG = "SELECT id_genero FROM jogo_genero WHERE id_jogo = :id";
                $stmtJG = $this->pdo->prepare(query: $sqlJG);
                $stmtJG->bindValue(param: ':id',   value: $jogo['id_jogo'], type: PDO::PARAM_INT);
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

        public function CreateImage($idJogo, $caminho, $tipo, $ordem_exib): bool
        {
            try {
                $this->pdo->beginTransaction();

                $sqlImagem = "
                    INSERT INTO jogo_imagem (
                        id_jogo, 
                        caminho,
                        tipo,
                        ordem_exib
                    ) 
                    VALUES (
                        :id_jogo, 
                        :caminho,
                        :tipo,
                        :ordem_exib
                    )
                ";
                $stmtImagem = $this->pdo->prepare(query: $sqlImagem);
                $stmtImagem->bindValue(param: ':id_jogo',       value: $idJogo,     type: PDO::PARAM_INT);
                $stmtImagem->bindValue(param: ':caminho',       value: $caminho,    type: PDO::PARAM_STR);
                $stmtImagem->bindValue(param: ':tipo',          value: $tipo,       type: PDO::PARAM_STR);
                $stmtImagem->bindValue(param: ':ordem_exib',    value: $ordem_exib, type: PDO::PARAM_INT);
                $stmtImagem->execute();

                $this->pdo->commit();
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                throw new PDOException(message: "Falha ao registrar imagens: ".$e->getMessage(), code: 0, previous: $e);
            }

            return true;
        }

        public function Existe($idJogo): bool {
            return $this->Read(idJogo: $idJogo) !== false;
        }

        public function UpdateImage($idJogo, $caminho, $tipo): bool {
            try {
                $this->pdo->beginTransaction();

                $sqlUpdate = "
                    UPDATE jogo_imagem SET
                        caminho = :caminho,
                        tipo = :tipo
                    WHERE id_jogo = :id_jogo
                ";

                $stmtUpdate = $this->pdo->prepare(query: $sqlUpdate);
                $stmtUpdate->bindValue(param: ':id_jogo', value: $idJogo, type: PDO::PARAM_INT);
                $stmtUpdate->bindValue(param: ':caminho', value: $caminho, type: PDO::PARAM_STR);
                $stmtUpdate->bindValue(param: ':tipo', value: $tipo, type: PDO::PARAM_STR);
                $stmtUpdate->execute();

                $this->pdo->commit();
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                throw new PDOException(message: "Falha ao atualizar imagem: ".$e->getMessage(), code: 0, previous: $e);
            }

            return true;
        }
    }