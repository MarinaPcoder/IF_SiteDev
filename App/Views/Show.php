<?php
    session_start();
    require_once '../../vendor/autoload.php';

    use App\Controllers\UsuarioController;
    $usuario = new UsuarioController;

    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }
    
    if (empty($_SESSION['Usuario'])) {
        header('Location: ./LoginUsuario.php');
        exit;
    } else {
        [$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);
        if (!$logado) {
            header(header: 'Location: ./logout.php');
            exit;
        }
        echo 'Id: '. $_SESSION['Usuario']['Id'];
        echo "<br>";
        echo 'Email: '. $_SESSION['Usuario']['Email'];
        var_dump($_SESSION);
    }