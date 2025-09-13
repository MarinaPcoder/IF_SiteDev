<?php 
    namespace App\Controllers;

    use App\Models\JogoCRUD;
    use App\Models\UsuarioCRUD;
    use App\Models\AvaliacaoCRUD;

    class AvaliacaoController {
        
        private int
            $id,
            $id_usuario,
            $id_jogo,
            $nota;

        private string 
            $justificativa;

        private JogoCRUD $jogo;
        private UsuarioCRUD $usuario;
        private AvaliacaoCRUD $avaliacao;

        Public function __construct() {
            $this -> jogo = new JogoCRUD;
            $this -> usuario = new UsuarioCRUD;
            $this -> avaliacao = new AvaliacaoCRUD;
        }

        public function Cadastrar($id_usuario, $id_jogo, $nota, $justificativa) {

            if($nota < 0 || $nota > 10) {
                $GLOBALS['erros']['Nota'][] = "Nota inválida.";
            }

            if ($justificativa == "") {
                $GLOBALS['erros']['Justificativa'][] = "Justificativa inválida.";
            }

            if (strlen($justificativa) < 10) {
                $GLOBALS['erros']['Justificativa'][] = "Justificativa muito curta.";
            }

            if (strlen($justificativa) > 2000) {
                $GLOBALS['erros']['Justificativa'][] = "Justificativa muito longa.";
            }

            if (empty($GLOBALS['erros'])) {
                $this -> SetIdUsuario($id_usuario);
                $this -> SetIdJogo($id_jogo);
                $this -> SetNota($nota);
                $this -> SetJustificativa($justificativa);

                $this -> avaliacao -> Create($this);
            }
        }

        public function Deletar($id) {
            return $this -> avaliacao -> Delete($id);
        }

        public function Atualizar($id, $nota, $justificativa): bool {

            if($nota < 0 || $nota > 10) {
                $GLOBALS['erros']['Nota'][] = "Nota inválida.";
            }

            if ($justificativa == "") {
                $GLOBALS['erros']['Justificativa'][] = "Justificativa inválida.";
            }

            if (strlen($justificativa) < 10) {
                $GLOBALS['erros']['Justificativa'][] = "Justificativa muito curta.";
            }

            if (strlen($justificativa) > 2000) {
                $GLOBALS['erros']['Justificativa'][] = "Justificativa muito longa.";
            }

            if (empty($GLOBALS['erros'])) {
                
                $this -> SetId($id);
                $this -> SetNota($nota);
                $this -> SetJustificativa($justificativa);

                $sucesso = $this -> avaliacao -> Update($this);

                return $sucesso;
            }

            return false;
        }

        public function Ler($id) {

            return $this -> avaliacao -> Read(id: $id);
        }

        private function SetId($id) {
            $this -> id = $id;
        }

        private function SetIdUsuario($id_usuario) {

            $this -> id_usuario = $id_usuario;
        }

        private function SetIdJogo($id_jogo) {

            $this -> id_jogo = $id_jogo;
        }

        private function SetNota($nota) {

            $this -> nota = $nota;
        }

        private function SetJustificativa($justificativa) {
            $this -> justificativa = $justificativa;
        }
        public function GetId() {
            return $this -> id;
        }
        public function GetIdUsuario() {
            return $this -> id_usuario;
        }

        public function GetIdJogo() {
            return $this -> id_jogo;
        }

        public function GetNota() {
            return $this -> nota;
        }

        public function GetJustificativa() {
            return $this -> justificativa;
        }

    }
