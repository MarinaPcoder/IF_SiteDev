<?php
    namespace App\Controllers;

    use DateTime;
    use Vtiful\Kernel\Format;

    class UsuarioController {
        private 
                $nome, 
                $email, 
                $nascimento, 
                $senhaCrip, 
                $bio;

        public function cadastrar($nome, $email, $datadenascimento, $senha, $senha2, $bio)
    {

        $erros = [];
        $errosMSG = [];

        // Validação

        // Verificação Nome
            if (!preg_match(pattern: "/^[a-zA-Z\s]+$/", subject: $nome)) {
                $erros['nome'][] = 'Formato de nome inválido: só é permitido letras minúsculas, maiúsculas e espaços em branco.';
            }

        // Verificação Email
            if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
                $erros['email'][] = 'Formato de email inválido.';
            }

        // Verificação Data
            $data = DateTime::createFromFormat('Y-m-d', $datadenascimento);
            if (!$data || $data->format('Y-m-d') !== $datadenascimento) {
                $erros['data'][] = 'Formato de data incorreto.';
            } else {
                list($ano, $mes, $dia) = explode(separator: '-', string: $datadenascimento);

                // data atual
                $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                // Descobre a unix timestamp da data de nascimento do fulano
                $mknascimento = mktime( 0, 0, 0, $mes, $dia, $ano);

                // cálculo
                $idade = floor((((($hoje - $mknascimento) / 60) / 60) / 24) / 365.25);

                if ($idade < 1 || $idade > 150) {
                $idade >= 150 ? 
                    $erros['data'][] = 'Idade máxima atingida'
                    : 
                    $erros['data'][] = 'Idade mínima atingida';
                }
            }

        // Verificação senha
        $pattern = '/^(?=.*[A-Z])      # pelo menos 1 maiúscula
              (?=.*[a-z])      # pelo menos 1 minúscula
              (?=.*\d)         # pelo menos 1 dígito
              [A-Za-z\d#?!@$%^&*\-] # letras, dígitos e símbolos permitidos
            /x';

        if ($senha !== $senha2) {
            $erros['senha2'][] = 'As senhas devem ser iguais';
        }

        if (strlen(string: $senha) > 30 || strlen(string: $senha) < 8 || !preg_match(pattern: $pattern, subject: $senha)) {
            if (strlen(string: $senha) <= 30) {
                $erros['senha'][] = 'Senha muito grande: máximo 30 caracteres.';
            }
            if (strlen(string: $senha) >= 8) {
                $erros['senha'][] = 'Senha muito pequena: mínimo 8 caracteres';
            }
            if (!preg_match(pattern: $pattern, subject: $senha)) {
                $erros['senha'][] = 'Formato incorreto: a senha deve ter pelo menos 1 dígito decimal, pelo menos 1 maiúscula, pelo menos 1 minúscula';
            }
        }

        // Sanitização
        $email = filter_var(value: $email, filter: FILTER_SANITIZE_EMAIL);
        $bio = htmlspecialchars(string: $bio);

        if (!empty($erros) || !empty($errosMSG)) {

            $_SESSION['msg_erro'] = $erros;
            $_SESSION['old_value'] = ['nomeusuario' => $nome, 'email' => $email, 'nascimento' => $datadenascimento, 'senha' => $senha, 'senha2' => $senha2, 'bio' => $bio];
            
            header(header: 'Location: ../views/cadastroUsuario.php');
            exit;
        } else {
            // Criptografia da senha
            $senhaCrip = md5(string: $senha);

            $this->SetNome(nome: $nome);
            $this->SetEmail(email: $email);
            $this->SetNascimento(nascimento: $datadenascimento);
            $this->SetSenhaCrip(senhaCrip: $senhaCrip);
            $this->SetBio(bio: $bio);
        }
        
    }
    
    private function SetNome($nome) {
        $this->nome = $nome;
    }

    private function SetEmail($email) {
        $this->email = $email;
    }

    private function SetNascimento($nascimento) {
        $this->nascimento = $nascimento;
    }

    private function SetSenhaCrip($senhaCrip) {
        $this->senhaCrip = $senhaCrip;
    }

    private function SetBio($bio) {
        $this->bio = $bio;
    }

    public function GetNome() {
        return $this->nome;
    }

    public function GetEmail() {
        return $this->email;
    }

    public function getDataNascimento() {
        return $this->nascimento;
    }

    public function GetSenhaCrip() {
        return $this->senhaCrip;
    }

    public function GetBio() {
        return $this->bio;
    }

    }