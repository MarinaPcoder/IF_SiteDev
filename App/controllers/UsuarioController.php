<?php
    namespace App\Controllers;

    use DateTime;
    use App\Models\UsuarioCRUD;
     

    class UsuarioController {
        private 
            $id,
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
    
        // Verificação Data
        list($datadenascimento, $errosNascimento) = $this->VerificarData($datadenascimento);

        $erros = array_merge($erros, $errosEmail, $errosSenha, $errosNascimento);
            

        // Sanitização
        $bio = htmlspecialchars(string: $bio);

        if (!empty($erros)) {

            $_SESSION['msg_erro'] = $erros;

        } else {
            // Criptografia da senha
            $senhaCrip = md5(string: $senha);

            $this->SetNome(nome: $nome);
            $this->SetEmail(email: $email);
            $this->SetNascimento(nascimento: $datadenascimento);
            $this->SetSenhaCrip(senhaCrip: $senhaCrip);
            $this->SetBio(bio: $bio);

            $usuarioCRUD = new UsuarioCRUD();
            $sucesso = $usuarioCRUD -> Create(usuario: $this);

            if ($sucesso) {
                
                $this->SessaoLogin($usuarioCRUD->GetId($this ->email), $this ->email);

                header(header: 'Location: ../../public/index.php');
                exit;
            }
        }
        
    }

    public function AtualizarUsuario($id, $nome, $email, $datadenascimento, $senha, $senha2, $bio)
    {
         $erros = [];

        // Validação

        // Verificação Nome
            if (!preg_match(pattern: "/^[a-zA-Z\s]+$/", subject: $nome)) {
                $erros['nome'][] = 'Formato de nome inválido: só é permitido letras minúsculas, maiúsculas e espaços em branco.';
            }

        list($email, $errosEmail)   = $this->VerificarEmail($email);
        list($senha, $errosSenha)   = $this->VerificarSenha($senha, $senha2);
    
        // Verificação Data
        list($datadenascimento, $errosNascimento) = $this->VerificarData($datadenascimento);

        $erros = array_merge($erros, $errosEmail, $errosSenha, $errosNascimento);
            

        // Sanitização
        $bio = htmlspecialchars(string: $bio);

        if (!empty($erros)) {

            $_SESSION['msg_erro'] = $erros;

        } else {
            // Criptografia da senha
            $senhaCrip = md5(string: $senha);

            $this->SetId            (id: $id);
            $this->SetNome          (nome: $nome);
            $this->SetEmail         (email: $email);
            $this->SetNascimento    (nascimento: $datadenascimento);
            $this->SetSenhaCrip     (senhaCrip: $senhaCrip);
            $this->SetBio           (bio: $bio);

            $usuarioCRUD = new UsuarioCRUD();
            $sucesso = $usuarioCRUD -> Update(usuario: $this);

            if ($sucesso) {

                unset($_SESSION['Usuario']);
                $this->SessaoLogin($usuarioCRUD->GetId($this ->email), $this ->email);

                header(header: 'Location: ../../public/index.php');
                exit;
            }
        }
    }

    public function ExcluirUsuario($id, $senha) {
        $usuarioCRUD = new UsuarioCRUD;
        $senhaBD = $usuarioCRUD-> Read(id: $id)[0]['senha'];

        if (md5($senha) == $senhaBD) {
            
            $sucesso = $usuarioCRUD->Delete($id);
            
            if ($sucesso) {

                unset($_SESSION['Usuario']);

                header(header: 'Location: ../../public/cadastroUsuario.php');
                exit;
            }

        } else {
            throw new \Exception(message: "Senha incorreta", code: 43);
            
        }
        
    }

    private function VerificarData($datadenascimento): array {
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

            return [$datadenascimento, $erros['data'] ?? []];
    }

    public function VerificarEmail($email): array{
        // Verificação Email
            if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
                $erros['email'][] = 'Formato de email inválido.';
            }

        return [filter_var(value: $email, filter: FILTER_SANITIZE_EMAIL), $erros['email'] ?? []];
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
            Echo 'Senhas errada';
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


        return [$senha, $erros['senha'] ?? []];
    }

    public function getUsuario($id): mixed {
        $usuario = new UsuarioCRUD;

        return $usuario -> Read(id: $id);
    }

    private function SetId($id): void {
        $this->id = $id;
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

    public function GetId() {
        return $this->id;
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
        $usuarioCRUD = new UsuarioCRUD;
        $id = $usuarioCRUD -> GetId(email: $email);

        if (!empty($id)) {
            $senhaBD = $usuarioCRUD-> Read(id: $id)[0]['senha'];
            $senha = md5(string: $senha);

            if ($senhaBD == $senha) {
                $this -> SessaoLogin(
                    id: $usuarioCRUD -> GetId(email: $email), 
                    email: $email
                );

                header(header: 'Location: ../../public/index.php');
                exit;
            } else {
                throw new \Exception(message: "Senha incorreta", code: 43);
                
            }
        } else {
            throw new \Exception("Usuario não encontrado: Email não cadastrado", 30);
            
        }
            
    }

    public function SessaoLogin($id, $email): void {
        $_SESSION['Usuario'] = [
            "Id" => $id,
            "Email" => $email
        ];
    }

    }