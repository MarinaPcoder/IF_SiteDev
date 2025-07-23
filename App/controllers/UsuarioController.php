<?php
    namespace App\Controllers;

    session_start();

    use App\models\Usuario;
    use DateTime;

    class UsuarioController {
        private 
        $nome, 
        $email, 
        $datadenascimento, 
        $senha, 
        $bio;

        public function cadastrar($nome, $email, $datadenascimento, $senha, $senha2, $bio)
    {

        $erros = [];
        // Validação
        if (!preg_match(pattern: "/^[a-zA-Z\s]+$/", subject: $nome)) {
            $_SESSION['msg_erro'] == '';
            $_SESSION['old_value'];
            header(header: 'Location: ../views/cadastroUsuario.php');
            exit;
        }

        if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
            // Tratar erro
        }

        $data = DateTime::createFromFormat('Y-m-d', $datadenascimento);
        if (!$data || $data->format('Y-m-d') !== $datadenascimento) {
            // Tratar erro
        }

        if (strlen($senha) < 8 || !preg_match("/[A-Za-z]/", $senha) || !preg_match("/\d/", $senha)) {
            // Tratar erro
        }

        // Sanitização
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $bio = filter_var($bio, FILTER_SANITIZE_STRING);

        // Criptografia da senha
        $senhaCrip = md5(string: $senha);

        if (empty()) {

        }
        
    }

    }