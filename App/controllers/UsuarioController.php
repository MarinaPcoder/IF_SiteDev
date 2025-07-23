<?php
    namespace App\Controllers;

    session_start();

    use App\models\Usuario;
    use DateTime;

    class UsuarioController {
        private $Usuario = new Usuario();

        public function cadastrar($nome, $email, $datadenascimento, $senha, $senha2, $bio)
    {

        $erros = [];

        // Validação
        if (!preg_match(pattern: "/^[a-zA-Z\s]+$/", subject: $nome)) {
            $erros['nome'][] = 'Formato de nome inválido: só é permitido letras minúsculas, maiúsculas e espaços em branco.';
        }

        if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
            // Tratar erro
        }

        $data = DateTime::createFromFormat('Y-m-d', $datadenascimento);
        if (!$data || $data->format('Y-m-d') !== $datadenascimento) {
            $erros['data'][] = 'Formato de data incorreto.';
        }

        if (strlen(string: $senha) <= 30 || !preg_match(pattern: "/[A-Za-z]/", subject: $senha) || !preg_match(pattern: "/\d/", subject: $senha)) {
            if (strlen(string: $senha) < 30) {
                $erros['senha'][] = 'Senha muito grande: máximo 30 caracteres.';
            }
            if (strlen(string: $senha) < 30) {
                $erros['senha'][] = '';
            }
        }

        // Sanitização
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $bio = htmlspecialchars($bio);

        // Criptografia da senha
        $senhaCrip = md5(string: $senha);

        if (!empty($erros) || !empty($errosMSG)) {

            $_SESSION['msg_erro'] = $erros;
            $_SESSION['old_value'] = ['nome' => $nome, 'email' => $email, 'nascimento' => $datadenascimento, 'senha' => $senha, 'senha2' => $senha2, 'bio' => $bio];
            
            session_destroy();
            header(header: 'Location: ../views/cadastroUsuario.php');
            exit;
        } else {
            self::$Usuario -> Cadastrar($nome, $email, $datadenascimento, $senha, $senha2, $bio);
        }
        
    }

    }