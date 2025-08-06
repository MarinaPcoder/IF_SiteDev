<?php

    namespace App\Controllers;

    use App\Models\JogoCRUD;

    class JogoController 
    {
        private 
            $id_jogo,
            $titulo,
            $descricao,
            $desenvolvedora,
            $data_lancamento,
            $link_compra,
            $plataforma;
        
        public function Cadastrar($titulo, $descricao, $desenvolvedora, $data_lancamento, $link_compra, $plataforma) {

            $erros = [];

            // filtro

            // sanitalização

            // validação

            
            if (empty($erros)) {
                // Definindo atributos
                $this -> SetTitulo(titulo: $titulo);
                $this -> SetDescricao(descricao: $descricao);
                $this -> SetDesenvolvedora(desenvolvedora: $desenvolvedora);
                $this -> SetDataLancamento(data_lancamento: $data_lancamento);
                $this -> SetLink(link_compra: $link_compra);
                $this -> SetPlataforma(plataforma: $plataforma);

                // Execução
                $usuarioCRUD = new JogoCRUD;
                $usuarioCRUD -> Create(jogo: $this);
            } else {
                // rotorna os erros

                return $erros;
            }

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
    }
    