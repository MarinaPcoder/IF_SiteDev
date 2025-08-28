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

        private UsuarioCRUD $usuarioCRUD;

        public function __construct() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $this->usuarioCRUD = new UsuarioCRUD();
            
        }

        public function cadastrar($nome, $email, $datadenascimento, $senha, $senha2, $bio){
            $erros = [];

            // Verificação Nome
            [$nome, $errosNome] = $this->VerificarNome($nome);

            // Verificação Email
            [$email, $errosEmail]   = $this->VerificarEmail($email);

            // Verificação Senha
            [$senha, $errosSenha]  = $this->VerificarSenha($senha, $senha2);
        
            // Verificação Data
            [$datadenascimento, $errosNascimento] = $this->VerificarData($datadenascimento);

            // Verificação Bio
            [$bio, $errosBio] = $this->VerificarBio(bio: $bio);

            // Sanitização
            $bio = htmlspecialchars(string: $bio);

            $erros = array_merge($erros,  $errosEmail, $errosSenha, $errosNascimento, $errosNome, $errosBio);


            if (!empty($erros)) {

                $GLOBALS['msg_erro'] = $erros;

            } else {
                // Criptografia da senha
                $senhaCrip = md5(string: $senha);

                $this->SetNome(nome: $nome);
                $this->SetEmail(email: $email);
                $this->SetNascimento(nascimento: $datadenascimento);
                $this->SetSenhaCrip(senhaCrip: $senhaCrip);
                $this->SetBio(bio: $bio);
                

                $sucesso = $this->usuarioCRUD->Create(usuario: $this);

                if ($sucesso) {

                    $this->SessaoLogin($this->usuarioCRUD->GetId($this->email), $this->email);

                    header(header: 'Location: ../../public/index.php');
                    exit;
                }
            }
        }

    public function AtualizarUsuario($id, $nome, $email, $datadenascimento, $senha, $senha2, $bio){
         $erros = [];

        // Validação

        // Verificação Nome
        [$nome, $errosNome] = $this->VerificarNome($nome);

        // Verificação Email
        [$email, $errosEmail]   = $this->VerificarEmail($email);

        // Verificação Senha
        [$senha, $errosSenha]   = $this->VerificarSenha($senha, $senha2);
    
        // Verificação Data
        [$datadenascimento, $errosNascimento] = $this->VerificarData($datadenascimento);

        // Verificação Bio
        [$bio, $errosBio] = $this->VerificarBio($bio);

        // Sanitização
        $bio = htmlspecialchars(string: $bio);

        $erros = array_merge($erros, $errosEmail, $errosSenha, $errosNascimento, $errosNome, $errosBio);

        if (!empty($erros)) {

            $GLOBALS['msg_erro'] = $erros;

        } else {
            // Criptografia da senha
            $senhaCrip = md5(string: $senha);

            $this->SetId            (id: $id);
            $this->SetNome          (nome: $nome);
            $this->SetEmail         (email: $email);
            $this->SetNascimento    (nascimento: $datadenascimento);
            $this->SetSenhaCrip     (senhaCrip: $senhaCrip);
            $this->SetBio           (bio: $bio);

            $sucesso = $this->usuarioCRUD->Update(usuario: $this);

            if ($sucesso) {

                unset($_SESSION['Usuario']);
                $this->SessaoLogin($this->usuarioCRUD->GetId($this->email), $this->email);

                header(header: 'Location: ../../public/index.php');
                exit;
            }
        }
    }

    public function ExcluirUsuario($id, $senha) {
        $senhaBD = $this->usuarioCRUD->Read(id: $id)[0]['senha'];

        switch (md5(string: $senha)) {
            case $senhaBD:
                $sucesso = $this->usuarioCRUD->Delete($id);
            
                if ($sucesso) {

                    unset($_SESSION['Usuario']);

                    header(header: 'Location: ./cadastroUsuario.php');
                    exit;
                }
                
                break;
            
            default:
                throw new \Exception(message: "Senha incorreta", code: 43);
        }
        
    }

    private function VerificarData($datadenascimento): array {

        $erros = [];

        $data = DateTime::createFromFormat('Y-m-d', $datadenascimento);

            if (!$data || $data->format('Y-m-d') !== $datadenascimento) {
                $erros[] = 'Formato de data incorreto.';
            } else {
                [$ano, $mes, $dia] = explode(separator: '-', string: $datadenascimento);

                // data atual
                $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                // Descobre a unix timestamp da data de nascimento do fulano
                $mknascimento = mktime( 0, 0, 0, $mes, $dia, $ano);

                // cálculo
                $idade = floor((((($hoje - $mknascimento) / 60) / 60) / 24) / 365.25);

                if ($idade < 1 || $idade > 150) {
                    
                    $idade > 150 ? 
                        $erros[] = 'Idade máxima atingida'
                        : 
                        $erros[] = 'Idade mínima atingida';
                }
            }

            return [$datadenascimento, empty($erros) ? [] : ['Data de Nascimento' => $erros ?? []]];
    }

    public function VerificarEmail($email): array{

        $erros = [];

        // Verificação Email
            if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'Formato de email inválido.';
            }

        return [filter_var(value: $email, filter: FILTER_SANITIZE_EMAIL), empty($erros) ? [] : ['Email' => $erros] ?? []];
    }

    public function VerificarSenha($senha, $senha2): array {

        $erros = [];

        // Verificação senha
        $pattern = '/^(?=.*[A-Z])      # pelo menos 1 maiúscula
              (?=.*[a-z])      # pelo menos 1 minúscula
              (?=.*\d)         # pelo menos 1 dígito
              [A-Za-z\d#?!@$%^&*\-] # letras, dígitos e símbolos permitidos
            /x';

        if ($senha !== $senha2) {
            $erros[] = 'As senhas devem ser iguais';
        }

        if (strlen(string: $senha) > 30 || strlen(string: $senha) < 8 || !preg_match(pattern: $pattern, subject: $senha)) {
            if (strlen(string: $senha) >= 30) {
                $erros[] = 'Senha muito grande: máximo 30 caracteres.';
            }
            if (strlen(string: $senha) <= 8) {
                $erros[] = 'Senha muito pequena: mínimo 8 caracteres';
            }
            if (!preg_match(pattern: $pattern, subject: $senha)) {
                $erros[] = 'Formato incorreto: a senha deve ter pelo menos 1 dígito decimal, pelo menos 1 maiúscula, pelo menos 1 minúscula';
            }
        }


        return [$senha, empty($erros) ? [] : ['Senha' => $erros ?? []]];
    }

    public function VerificarNome($nome): array {

        $erros = [];

        // Tira espaço
        $nome = trim(preg_replace('/\s+/', ' ', $nome));

        // Verificação de tamanho
        if (strlen($nome) < 2 || strlen($nome) > 60) {
            $erros['Nome'][] = 'O nome deve ter entre 2 e 60 caracteres.';
        }

        // Verificação de formato
        if (!preg_match(pattern: "/^[\p{L}\s'-]+$/u", subject: $nome)) {
            $erros['Nome'][] = 'Formato de nome inválido: só é permitido letras (inclusive acentuadas), espaços, hífen e apóstrofo.';
        }

        return [$nome, empty($erros) ? [] : ["Nome" => $erros]];
    }

    public function VerificarBio($bio): array {

        $erros = [];

        if (!is_string($bio)) {
            $erros['Bio'][] = 'Formato de bio inválido.';
        }
        if (strlen(string: $bio) > 1000) {
            $erros['Bio'][] = 'Bio muito grande: máximo 1000 caracteres.';
        }

        return [$bio, empty($erros) ? [] : ["Bio" => $erros]];
    }
    public function getUsuario($id): mixed {

        return $this->usuarioCRUD->Read(id: $id);
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

            switch ($senhaBD) {
                case $senha:
                    $this -> SessaoLogin(
                    id: $usuarioCRUD -> GetId(email: $email), 
                    email: $email
                    );

                    header(header: 'Location: ../../public/index.php');
                    exit;
                
                default:
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

    public function ConfereLogin($id): array {

        $logado = !empty($this->usuarioCRUD->Read($id));
        $tipo_usuario = $this->usuarioCRUD->Read($id)[0]['tipo_perfil'];

        return [$logado, $tipo_usuario];
    }

    }