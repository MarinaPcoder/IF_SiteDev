<?php
    namespace App\Controllers;

    use DateTime;

    class UsuarioController {
        private $nome, $email, $nascimento, $senhaCrip, $bio;

        public function cadastrar($nome, $email, $datadenascimento, $senha, $senha2, $bio)
    {
        session_start();

        $erros = [];

        // Validação
        if (!preg_match(pattern: "/^[a-zA-Z\s]+$/", subject: $nome)) {
            $erros['nome'][] = 'Formato de nome inválido: só é permitido letras minúsculas, maiúsculas e espaços em branco.';
        }

        if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
            $erros['email'][] = 'Formato de email inválido.';
        }

        $data = DateTime::createFromFormat('Y-m-d', $datadenascimento);
        if (!$data || $data->format('Y-m-d') !== $datadenascimento) {
            $erros['data'][] = 'Formato de data incorreto.';
        } else {
            list($ano, $mes, $dia) = explode(separator: '-', string: $datadenascimento);

            // data atual
            $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            // Descobre a unix timestamp da data de nascimento do fulano
            $nascimento = mktime( 0, 0, 0, $mes, $dia, $ano);

            // cálculo
            $idade = floor((((($hoje - $nascimento) / 60) / 60) / 24) / 365.25);
        }

        if ($idade < 1 || $idade >= 150) {
            $idade >= 150 ? 
                $erros['data'][] = 'Idade máxima atingida'
                : 
                $erros['data'][] = 'Idade mínima atingida';
        }

        $pattern = '/^(?=.*[A-Z])      # pelo menos 1 maiúscula
              (?=.*[a-z])      # pelo menos 1 minúscula
              (?=.*\d)         # pelo menos 1 dígito
              [A-Za-z\d#?!@$%^&*\-] # letras, dígitos e símbolos permitidos
            /x';

        if ($senha !== $senha2) {
            $erros['senha2'][] = 'As senhas devem ser iguais';
        }

        if (strlen(string: $senha) <= 30 || strlen(string: $senha) >= 8 || !preg_match(pattern: $pattern, subject: $senha)) {
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
            $_SESSION['old_value'] = ['nome' => $nome, 'email' => $email, 'nascimento' => $datadenascimento, 'senha' => $senha, 'senha2' => $senha2, 'bio' => $bio];
            
            
            header(header: 'Location: ../views/cadastroUsuario.php');
            exit;
        } else {
            // Criptografia da senha
            $senhaCrip = md5(string: $senha);

            self::SetNome(nome: $nome);
            self::SetEmail(email: $email);
            self::SetNascimento(nascimento: $nascimento);
            self::SetSenhaClip(senhaCrip: $senhaCrip);
            self::SetBio(bio: $bio);
        }
        
    }
    
    private function SetNome($nome) {
        self::$nome = $nome;
    }

    private function SetEmail($email) {
        self::$email = $email;
    }

    private function SetNascimento($nascimento) {
        self::$nascimento = $nascimento;
    }

    private function SetSenhaClip($senhaCrip) {
        self::$senhaCrip = $senhaCrip;
    }

    private function SetBio($bio) {
        self::$bio = $bio;
    }

    private function GetNome() {
        return self::$nome;
    }

    private function GetEmail() {
        return self::$email;
    }

    private function getDataNascimento() {
        return self::$nascimento;
    }

    private function GetSenhaClip() {
        return self::$senhaCrip;
    }

    private function GetBio() {
        return self::$bio;
    }

    }