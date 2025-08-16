<?php

    namespace App\Controllers;

    use App\Models\JogoCRUD;
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

                list($ano, $mes, $dia) = explode(separator: '-', string: $dados['data_lancamento']);
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
            $usuarioCRUD = new JogoCRUD;
            $usuarioCRUD -> Create(jogo: $this);

            return [];
        }

        public function Atualizar($id, $titulo, $descricao, $desenvolvedora, $data_lancamento, $link_compra, $plataforma, $genero): array {

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

                list($ano, $mes, $dia) = explode(separator: '-', string: $dados['data_lancamento']);
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
            $this -> SetId(id: $id);
            $this -> SetTitulo(titulo: $dados['titulo']);
            $this -> SetDescricao(descricao: $dados['descricao']);
            $this -> SetDesenvolvedora(desenvolvedora: $dados['desenvolvedora']);
            $this -> SetDataLancamento(data_lancamento: $dados['data_lancamento']);
            $this -> SetLink(link_compra: $dados['link_compra']);
            $this -> SetPlataforma(plataforma: $dados['plataforma']);
            $this -> SetGenero(genero: $genero);

            // Execução
            $usuarioCRUD = new JogoCRUD;
            $usuarioCRUD -> Update(jogo: $this);

            return [];
        }

        public function GetJogo($id) {
            $jogoCRUD = new JogoCRUD;
            return $jogoCRUD -> Read( $id);
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
    