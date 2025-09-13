<?php

    namespace App\Controllers;

    use App\Models\JogoCRUD;
    use App\Models\UsuarioCRUD;
    use DateTime;

    class JogoController 
    {
        private 
            $id_jogo,
            $titulo,
            $descricao,
            $desenvolvedora,
            $data_lancamento,
            $link_compra,
            $plataforma,
            $genero;

        private JogoCRUD $jogoCRUD;
        private UsuarioCRUD $usuarioCRUD;

        public function  __construct() {
            $this -> jogoCRUD = new JogoCRUD;
            $this -> usuarioCRUD = new UsuarioCRUD;
        }
        
        public function Cadastrar($titulo, $descricao, $desenvolvedora, $data_lancamento, $link_compra, $plataforma, $genero): array {

            $erros = [];

            $norm = static function (?string $v, int $max = 255) {
                $v ??= '';
                $v = trim(string: preg_replace(pattern: '/\s+/u', replacement: ' ', subject: $v));
                // evita payloads enormes
                return mb_substr(string: $v, start: 0, length: $max);
            };

            $dados = [
                'titulo'          => $norm(v: $titulo,          max: 255),
                'descricao'       => $norm(v: $descricao,       max: 5000),
                'desenvolvedora'  => $norm(v: $desenvolvedora,  max: 255),
                'data_lancamento' => $norm(v: $data_lancamento, max: 10),
                'link_compra'     => $norm(v: $link_compra,     max: 500),
                'plataforma'      => $norm(v: $plataforma,      max: 50),
                'genero'          => $norm(v: $genero,          max: 25)
            ];
            
            // validação
                // Título: 2–255, sem somente símbolos
                if ($dados['titulo'] === '' || mb_strlen($dados['titulo']) < 2 || mb_strlen($dados['titulo']) > 255) {
                    $erros['titulo'][] = 'Informe um título entre 2 e 255 caracteres.';
                }

                // Plataforma: whitelist
                $plataformasPermitidas = ['pc','ps5','ps4','one','xboxS','xboxX','switch'];
                if (!in_array(needle: $dados['plataforma'], haystack: $plataformasPermitidas, strict: true)) {
                    $erros['plataforma'][] = 'Plataforma inválida.';
                }

                // Data: formato YYYY-MM-DD e data real
                $dt = DateTime::createFromFormat('Y-m-d', $dados['data_lancamento']);
                $dataValida = $dt && $dt->format('Y-m-d') === $dados['data_lancamento'];
                if (!$dataValida) {
                    $erros['data_lancamento'][] = 'Data inválida (use YYYY-MM-DD).';
                }

                [$ano, $mes, $dia] = explode(separator: '-', string: $dados['data_lancamento']);
                $mkLancamento = mktime(0, 0, 0, $mes, $dia, $ano);
                $mkHoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                if ($mkLancamento >= $mkHoje) {
                    $erros['data_lancamento'][] = 'Data inválida.';
                } else if ($ano < 1900) {
                    $erros['data_lancamento'][] = 'Data inválida.';
                }

                // Desenvolvedora: 2–120 (você pode afrouxar os caracteres aceitos conforme necessidade)
                if ($dados['desenvolvedora'] === '' || mb_strlen($dados['desenvolvedora']) < 2 || mb_strlen($dados['desenvolvedora']) > 120) {
                    $erros['desenvolvedora'][] = 'Informe a desenvolvedora (2–255).';
                }

                // URL de compra: http(s) válida
                if ($dados['link_compra'] !== '' && !filter_var(value: $dados['link_compra'], filter: FILTER_VALIDATE_URL)) {
                    $erros['link_compra'][] = 'URL de compra inválida.';
                }

                // Descrição: comprimento mínimo opcional
                if (mb_strlen(string: $dados['descricao']) < 10) {
                    $erros['descricao'][] = 'Descrição muito curta (mín. 10 caracteres).';
                }

                // genero
                if (!is_int($genero) and !in_array(needle: $genero, haystack: [1, 2, 3, 4, 5, 6, 7]) ) {
                    $erros['genero'][] = "Gênero indefinido.";
                }


            if (!empty($erros)) {
                return $erros; // devolva pro controller da rota exibir no form
            }
            
            // Definindo atributos
            $this -> SetTitulo(titulo: $dados['titulo']);
            $this -> SetDescricao(descricao: $dados['descricao']);
            $this -> SetDesenvolvedora(desenvolvedora: $dados['desenvolvedora']);
            $this -> SetDataLancamento(data_lancamento: $dados['data_lancamento']);
            $this -> SetLink(link_compra: $dados['link_compra']);
            $this -> SetPlataforma(plataforma: $dados['plataforma']);
            $this -> SetGenero(genero: $genero);

            // Execução
            $this->jogoCRUD-> Create(jogo: $this);

            return [];
        }

        public function Atualizar($id, $titulo, $descricao, $desenvolvedora, $data_lancamento, $link_compra, $plataforma, $genero): array|bool {

            $erros = [];

            $norm = static function (?string $v, int $max = 255) {
                $v ??= '';
                $v = trim(string: preg_replace(pattern: '/\s+/u', replacement: ' ', subject: $v));
                // evita payloads enormes
                return mb_substr(string: $v, start: 0, length: $max);
            };

            $dados = [
                'titulo'          => $norm(v: $titulo,          max: 255),
                'descricao'       => $norm(v: $descricao,       max: 5000),
                'desenvolvedora'  => $norm(v: $desenvolvedora,  max: 255),
                'data_lancamento' => $norm(v: $data_lancamento, max: 10),
                'link_compra'     => $norm(v: $link_compra,     max: 500),
                'plataforma'      => $norm(v: $plataforma,      max: 50)
            ];
            
            // validação
                // Título: 2–255, sem somente símbolos
                if ($dados['titulo'] === '' || mb_strlen($dados['titulo']) < 2 || mb_strlen($dados['titulo']) > 255) {
                    $erros['titulo'][] = 'Informe um título entre 2 e 255 caracteres.';
                }

                // Plataforma: whitelist
                $plataformasPermitidas = ['pc','ps5','ps4','one','xboxS','xboxX','switch'];
                if (!in_array(needle: $dados['plataforma'], haystack: $plataformasPermitidas, strict: true)) {
                    $erros['plataforma'][] = 'Plataforma inválida.';
                }

                // Data: formato YYYY-MM-DD e data real
                $dt = DateTime::createFromFormat('Y-m-d', $dados['data_lancamento']);
                $dataValida = $dt && $dt->format('Y-m-d') === $dados['data_lancamento'];
                if (!$dataValida) {
                    $erros['data_lancamento'][] = 'Data inválida (use YYYY-MM-DD).';
                }

                [$ano, $mes, $dia] = explode(separator: '-', string: $dados['data_lancamento']);
                $mkLancamento = mktime(0, 0, 0, $mes, $dia, $ano);
                $mkHoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                if ($mkLancamento >= $mkHoje) {
                    $erros['data_lancamento'][] = 'Data inválida.';
                } else if ($ano < 1900) {
                    $erros['data_lancamento'][] = 'Data inválida.';
                }

                // Desenvolvedora: 2–120 (você pode afrouxar os caracteres aceitos conforme necessidade)
                if ($dados['desenvolvedora'] === '' || mb_strlen($dados['desenvolvedora']) < 2 || mb_strlen($dados['desenvolvedora']) > 255) {
                    $erros['desenvolvedora'][] = 'Informe a desenvolvedora (2–255).';
                }

                // URL de compra: http(s) válida
                if ($dados['link_compra'] !== '' && !filter_var(value: $dados['link_compra'], filter: FILTER_VALIDATE_URL)) {
                    $erros['link_compra'][] = 'URL de compra inválida.';
                }

                // Descrição: comprimento mínimo opcional
                if (mb_strlen(string: $dados['descricao']) < 10) {
                    $erros['descricao'][] = 'Descrição muito curta (mín. 10 caracteres).';
                }

                // genero
                if (!is_int($genero) and !in_array(needle: $genero, haystack: [1, 2, 3, 4, 5, 6, 7]) ) {
                    $erros['genero'][] = "Gênero indefinido.";
                }


            if (!empty($erros)) {
                return $erros; // devolve pra exibir no form
            }
            
            // Definindo atributos
            $this -> SetId(id: $id);
            $this -> SetTitulo(titulo: $dados['titulo']);
            $this -> SetDescricao(descricao: $dados['descricao']);
            $this -> SetDesenvolvedora(desenvolvedora: $dados['desenvolvedora']);
            $this -> SetDataLancamento(data_lancamento: $dados['data_lancamento']);
            $this -> SetLink(link_compra: $dados['link_compra']);
            $this -> SetPlataforma(plataforma: $dados['plataforma']);
            $this -> SetGenero(genero: $genero);

            // Execução
            $sucesso = $this->jogoCRUD -> Update(jogo: $this);

            if (!$sucesso) {
                return $sucesso;
            }

            return [];
        }

        public function Deletar($id, $id_usuario, $senhaForm) {
            $senhaBD = $this -> usuarioCRUD -> Read(id: $id_usuario)[0]['senha'];

            $senhaForm = md5(string: $senhaForm);

            switch ($senhaForm) {
                case $senhaBD:
                    $this -> jogoCRUD -> Delete(id: $id);

                    header(header: 'Location: ../../public');
                    exit;
                
                default:
                    throw new \Exception(message: "Senhas não são iguais", code: 1);
            }
        }

        public function UploadImagens($idJogo, $poster, $banner, $screenshots, $ordemScreenshots): array {

            $erros = [];

            // cria diretório se necessário
            $basePublic = realpath(__DIR__ . '/../../public') ?: __DIR__ . '/../../public';
            $dir = $basePublic . '/uploads';
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }

            // Validação e upload do poster
            if ($poster && $poster['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($poster['name'], PATHINFO_EXTENSION));
                $novoNome = "poster_{$idJogo}.{$ext}";
                $destino = "{$dir}/{$novoNome}";

                if (!move_uploaded_file($poster['tmp_name'], $destino)) {
                    $erros['poster'][] = 'Erro ao fazer upload do poster.';
                } else {
                    // Registrar o caminho do poster no banco de dados
                    $caminho = "/uploads/$novoNome";
                    $this->jogoCRUD->UpdateImage(idJogo: $idJogo, caminho: $caminho, tipo: 'poster');
                }
            }

            // Validação e upload do banner
            if ($banner && $banner['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($banner['name'], PATHINFO_EXTENSION));
                $novoNome = "banner_{$idJogo}.{$ext}";
                $destino = "{$dir}/{$novoNome}";

                if (!move_uploaded_file($banner['tmp_name'], $destino)) {
                    $erros['banner'][] = 'Erro ao fazer upload do banner.';
                } else {
                    // Registrar o caminho do banner no banco de dados
                    $caminho = "/uploads/$novoNome";
                    $this->jogoCRUD->UpdateImage(idJogo: $idJogo, caminho: $caminho, tipo: 'banner');
                }
            }

            
            // SCREENSHOTS – se quiser casar 100% com a ordem do BD:
            if ($screenshots) {
                foreach ($screenshots['tmp_name'] as $k => $tmp) {
                    if ($screenshots['error'][$k] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($screenshots['name'][$k], PATHINFO_EXTENSION));
                        // Opção A (simples): salva com timestamp único
                        $novoNome = "screenshots_{$idJogo}_" . time() . ".ordem({$ordemScreenshots}).{$ext}";
                        $destino = "{$dir}/{$novoNome}";

                        if (move_uploaded_file($tmp, $destino)) {
                            $this->jogoCRUD->CreateImage(idJogo:$idJogo, caminho:"/uploads/{$novoNome}", tipo:'screenshot', ordemScreenshots: $ordemScreenshots);
                        } else {
                            $erros['screenshots'][] = "Falha no upload da screenshot #{$k}.";
                        }
                        $ordemScreenshots++;
                    }
                }
            }

            return $erros;
        }

        public function ListarGeneros(): array {
            return $this->jogoCRUD->ListGenres();
        }

        public function LerImagem($idImagem): mixed {
            return $this->jogoCRUD->ReadImage(idImagem: $idImagem);
        }

        public function DeletarImagem($id, $caminho, $ordem, $id_jogo) {

            $sucesso = $this->jogoCRUD->DeleteImage($id, $caminho, $ordem, $id_jogo);

            return $sucesso;
        }

        public function LerJogo($idJogo): mixed {
            return $this->jogoCRUD->Read(idJogo: $idJogo);
        }

        public function ExisteJogo($idJogo) {
            return $this->jogoCRUD->Existe(idJogo: $idJogo);
        }

        public function GetJogoPorTituloEPlataforma($titulo, $plataforma) {    
            return $this -> jogoCRUD -> ReadByTitleAndPlatform(titulo: $titulo, plataforma: $plataforma);
        }

        public function GetJogo($id) {
            return $this -> jogoCRUD -> Read( $id);
        }

        private function SetId($id) {
            $this -> id_jogo = $id;
        }
        private function SetTitulo($titulo) {
            $this -> titulo = $titulo;
        }

        private function SetDescricao($descricao) {
            $this -> descricao = $descricao;
        }

        private function SetDesenvolvedora($desenvolvedora) {
            $this -> desenvolvedora = $desenvolvedora;
        }

        private function SetDataLancamento($data_lancamento) {
            $this -> data_lancamento = $data_lancamento;
        }

        private function SetLink($link_compra) {
            $this -> link_compra = $link_compra;
        }  

        private function SetPlataforma($plataforma) {
            $this -> plataforma = $plataforma;
        }

        public function SetGenero($genero) {
            $this -> genero = $genero;
        }

        public function GetId() {
            return $this->id_jogo;
        }

        public function GetTitulo() {
            return $this->titulo;
        }

        public function GetDescricao() {
            return $this->descricao;
        }

        public function GetDesenvolvedora() {
            return $this->desenvolvedora;
        }

        public function GetDataLancamento() {
            return $this->data_lancamento;
        }

        public function GetLink() {
            return $this->link_compra;
        }

        public function GetPlataforma() {
            return $this->plataforma;
        }

        public function GetGenero() {
            return $this->genero;
        }
    }
    