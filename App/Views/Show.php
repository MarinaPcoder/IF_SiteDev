<?php
    session_start();
    require_once '../../vendor/autoload.php';

    use App\Controllers\UsuarioController;
    $usuario = new UsuarioController;


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
    }