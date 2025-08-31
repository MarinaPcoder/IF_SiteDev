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

                $sqlExistente = "
                    SELECT COUNT(*) FROM jogo
                    WHERE titulo = :titulo AND plataforma = :plataforma
                ";
                $stmtExistente = $this->pdo->prepare(query: $sqlExistente);
                $stmtExistente->bindValue(param: ':titulo', value: $jogo->getTitulo(), type: PDO::PARAM_STR);
                $stmtExistente->bindValue(param: ':plataforma', value: $jogo->getPlataforma(), type: PDO::PARAM_STR);
                $stmtExistente->execute();

                $existe = (bool) $stmtExistente->fetchColumn();

                if ($existe) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Jogo já existe.");
                }

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
                $sucesso = $stmtJogo->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao inserir jogo.");
                }

                // ultimo id inserido no BD
                $idJogo = (int) $this->pdo->lastInsertId();

                // Insere na tabela jogo_genero
                $sqlJG = "INSERT INTO Jogo_Genero (id_jogo, id_genero) VALUES (:id_jogo, :id_genero)";
                $stmtJG = $this->pdo->prepare(query: $sqlJG);
                $stmtJG->bindValue(param: ':id_jogo',   value: $idJogo,              type: PDO::PARAM_INT);
                $stmtJG->bindValue(param: ':id_genero', value: $jogo->GetGenero(),  type: PDO::PARAM_INT);
                $sucesso =$stmtJG->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao inserir gênero.");
                }

                // Define poster Padrão
                $sqlPoster = "
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

                $stmtPoster = $this->pdo->prepare(query: $sqlPoster);
                $stmtPoster->bindValue(param: ':id_jogo', value: $idJogo, type: PDO::PARAM_INT);
                $stmtPoster->bindValue(param: ':caminho', value: '/assets/img/poster.png', type: PDO::PARAM_STR);
                $stmtPoster->bindValue(param: ':tipo', value: 'poster', type: PDO::PARAM_STR);
                $stmtPoster->bindValue(param: ':ordem_exib', value: 0, type: PDO::PARAM_INT);
                $sucesso = $stmtPoster->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao inserir imagem do poster.");
                }

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
                $stmtBanner->bindValue(param: ':caminho', value: '/assets/img/banner.png', type: PDO::PARAM_STR);
                $stmtBanner->bindValue(param: ':tipo', value: 'banner', type: PDO::PARAM_STR);
                $stmtBanner->bindValue(param: ':ordem_exib', value: 0, type: PDO::PARAM_INT);
                $sucesso = $stmtBanner->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao inserir imagem do banner.");
                }

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
                $sucesso = $stmtJG->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao ler gênero do jogo.");
                }

                $genero = $stmtJG->fetch(PDO::FETCH_ASSOC);

                if (!$genero) {
                    $this->pdo->rollBack(); 
                    return False; 
                }

                $jogo['generos'] = $genero;

                $sqlPoster = "
                SELECT caminho, id_imagem
                FROM jogo_imagem 
                WHERE id_jogo = :id AND tipo = 'poster'
                ";
                $stmtPoster = $this->pdo->prepare(query: $sqlPoster);
                $stmtPoster->bindValue(param: ':id', value: $idJogo, type: PDO::PARAM_INT);
                $sucesso = $stmtPoster->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao ler imagem do poster.");
                }

                $jogo['poster'] = $stmtPoster->fetchColumn();

                if (!$jogo['poster']) {
                    $this->pdo->rollBack();
                    return False;
                }

                $sqlBanner = "
                SELECT 
                    caminho, 
                    id_imagem
                FROM 
                    jogo_imagem 
                WHERE 
                    id_jogo = :id AND tipo = 'banner'
                ";

                $stmtBanner = $this->pdo->prepare(query: $sqlBanner);
                $stmtBanner->bindValue(param: ':id', value: $idJogo, type: PDO::PARAM_INT);
                $stmtBanner->execute();
                $jogo['banner'] = $stmtBanner->fetchColumn();

                if (!$jogo['banner']) {
                    $this->pdo->rollBack();
                    return False;
                }

                $stmtScreenshot = $this->pdo->prepare(query: "
                SELECT 
                caminho, id_imagem
                FROM jogo_imagem 
                WHERE id_jogo = :id AND tipo = 'screenshot'
                ");
                $stmtScreenshot->bindValue(param: ':id', value: $idJogo, type: PDO::PARAM_INT);
                $stmtScreenshot->execute();
                $jogo['screenshots'] = $stmtScreenshot->fetchAll(mode: PDO::FETCH_ASSOC);

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
                $sucesso =$stmtJogo->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao editar jogo.");
                }

                $sqlJG = "
                UPDATE jogo_genero

                SET id_genero = :id_genero

                WHERE id_jogo = :id_jogo
                ";

                $stmtJG = $this->pdo->prepare(query: $sqlJG);
                $stmtJG -> bindValue(param: ':id_genero',   value: $jogo->GetGenero(),  type: PDO::PARAM_INT);
                $stmtJG -> bindValue(param: ':id_jogo',     value: $jogo->GetId(),      type: PDO::PARAM_INT);
                $sucesso =$stmtJG ->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao editar gênero.");
                }

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

                // Deleta Imagens
                $sqlImagens = "
                DELETE FROM jogo_imagem
                WHERE id_jogo = :id
                ";
                $stmtImagens = $this->pdo->prepare(query: $sqlImagens);
                $stmtImagens->bindValue(param: ":id", value: $id, type: PDO::PARAM_INT);
                $sucesso = $stmtImagens->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao deletar imagens do jogo.");
                }

                // Deleta Gêneros
                $sqlJG = "
                DELETE FROM jogo_genero

                WHERE
                    id_jogo = :id
                ";

                $stmtJG = $this->pdo->prepare(query: $sqlJG);
                $stmtJG -> bindValue(param: ":id", value: $id, type: PDO::PARAM_INT);
                $sucesso =$stmtJG -> execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao deletar gênero do jogo.");
                }

                // Deleta Jogo
                $sqlJogo = "
                DELETE FROM jogo 
                
                WHERE   
                    id_jogo = :id
                ";

                $stmtJogo = $this -> pdo -> prepare(query: $sqlJogo);
                $stmtJogo -> bindValue(param: ":id", value: $id, type: PDO::PARAM_INT);
                $sucesso = $stmtJogo->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao deletar jogo.");
                }

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

                $sqlPoster = "
                SELECT caminho 
                FROM jogo_imagem 
                WHERE id_jogo = :id AND tipo = 'poster'
                ";
                $stmtPoster = $this->pdo->prepare(query: $sqlPoster);
                $stmtPoster->bindValue(param: ':id', value: $jogo['id_jogo'], type: PDO::PARAM_INT);
                $sucesso = $stmtPoster->execute();

                if (!$sucesso) {
                    $this->pdo->rollBack();
                    throw new PDOException(message: "Falha ao ler imagem do poster.");
                }

                $jogo['poster'] = $stmtPoster->fetchColumn();

                if (!$jogo['poster']) {
                    $this->pdo->rollBack();
                    return False;
                }

                $sqlBanner = "
                SELECT caminho 
                FROM jogo_imagem 
                WHERE id_jogo = :id AND tipo = 'banner'
                ";

                $stmtBanner = $this->pdo->prepare(query: $sqlBanner);
                $stmtBanner->bindValue(param: ':id', value: $jogo['id_jogo'], type: PDO::PARAM_INT);
                $stmtBanner->execute();
                $jogo['banner'] = $stmtBanner->fetchColumn();

                if (!$jogo['banner']) {
                    $this->pdo->rollBack();
                    return False;
                }

                $stmtScreenshot = $this->pdo->prepare(query: "
                SELECT caminho 
                FROM jogo_imagem 
                WHERE id_jogo = :id AND tipo = 'screenshot'
                ");
                $stmtScreenshot->bindValue(param: ':id', value: $jogo['id_jogo'], type: PDO::PARAM_INT);
                $stmtScreenshot->execute();
                $jogo['screenshots'] = $stmtScreenshot->fetchAll(PDO::FETCH_COLUMN);

                if (!$jogo['screenshots']) {
                    $this->pdo->rollBack();
                    return False;
                }

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
                        caminho = :caminho
                    WHERE id_jogo = :id_jogo AND tipo = :tipo
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

        public function DeleteImage($id): bool {
            try {
                $this->pdo->beginTransaction();

                $sqlDelete = "
                    DELETE FROM jogo_imagem WHERE id_imagem = :id AND tipo = 'screenshot'
                ";
                $stmtDelete = $this->pdo->prepare(query: $sqlDelete);
                $stmtDelete->bindValue(param: ':id', value: $id, type: PDO::PARAM_INT);
                $stmtDelete->execute();

                $this->pdo->commit();
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                throw new PDOException(message: "Falha ao deletar imagem: ".$e->getMessage(), code: 0, previous: $e);
            }

            return true;
        }
    }