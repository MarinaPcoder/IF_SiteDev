<?php
    session_start();

    if (!isset($_SESSION['Usuario'])) {
        header('Location: ./LoginUsuario.php');
        exit;
    } else {
        echo 'Id: '. $_SESSION['Usuario']['Id'];
        echo "<br>";
        echo 'Email: '. $_SESSION['Usuario']['Email'];
    }