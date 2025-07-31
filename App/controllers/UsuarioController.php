<?php
    namespace App\Controllers;

    use DateTime;
    use App\Models\UsuarioCRUD;

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

        // Validação

        // Verificação Nome
            if (!preg_match(pattern: "/^[a-zA-Z\s]+$/", subject: $nome)) {
                $erros['nome'][] = 'Formato de nome inválido: só é permitido letras minúsculas, maiúsculas e espaços em branco.';
            }

        list($email, $errosEmail)   = $this->VerificarEmail($email);
        list($senha, $errosSenha)   = $this->VerificarSenha($senha, $senha2);
        
        $erros = array_merge($erros, $errosEmail, $errosSenha);

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


        // Sanitização
        $bio = htmlspecialchars(string: $bio);

        if (!empty($erros)) {

            $_SESSION['msg_erro'] = $erros;

            
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

    public function VerificarEmail($email): array{
        // Verificação Email
            if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
                $erros['email'][] = 'Formato de email inválido.';
            }

        $email = [filter_var(value: $email, filter: FILTER_SANITIZE_EMAIL), $erros['email'] ?? []];

        return $email;
    }

    public function VerificarSenha($senha, $senha2): array {
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
            if (strlen(string: $senha) >= 30) {
                $erros['senha'][] = 'Senha muito grande: máximo 30 caracteres.';
            }
            if (strlen(string: $senha) <= 8) {
                $erros['senha'][] = 'Senha muito pequena: mínimo 8 caracteres';
            }
            if (!preg_match(pattern: $pattern, subject: $senha)) {
                $erros['senha'][] = 'Formato incorreto: a senha deve ter pelo menos 1 dígito decimal, pelo menos 1 maiúscula, pelo menos 1 minúscula';
            }
        }

        $senha = [$senha, $erros['senha'] ?? []];

        return $senha;
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

    public function Login($email, $senha): void {
        $usuario = new UsuarioCRUD;
        $senhaBD = $usuario-> Read(id: $usuario -> GetId(email: $email)[0]['id_usuario'])[0]['senha'];
        $senha = md5(string: $senha);

        if ($senhaBD == $senha) {
            $this -> SessaoLogin(
                id: $usuario -> GetId(email: $email)[0]['id_usuario'], 
                email: $email
            );

            header(header: 'Location: ../../public/index.php');
            exit;
        }
    }

    public function SessaoLogin($id, $email): void {
        $_SESSION['Usuario'] = [
            "Id" => $id,
            "Email" => $email
        ];
    }

    }