<?php 
    require_once '../../vendor/autoload.php';

    $titulo = 'Registro';
    require_once '../../public/assets/components/head.php';
    
    session_start();
?>

</head>

<?php 
    use App\Controllers\UsuarioController;
    use App\models\UsuarioCRUD;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        try {
            $usuario = new UsuarioController();
            $usuario ->cadastrar(
                nome: $_POST['nomeusuario'], 
                email: $_POST['email'], 
                datadenascimento: $_POST['nascimento'], 
                senha: $_POST['senha'], 
                senha2: $_POST['senha2'], 
                bio: $_POST['bio']
            );

            if (!(isset($_SESSION['msg_erro']) || isset($_SESSION['msg_erro']))) {

                $usuarioCRUD = new UsuarioCRUD();
                $usuarioCRUD -> Create(usuario: $usuario);

            }
            
        } catch (PDOException $e) {
            
            error_log("Erro PDO: " . $e->getMessage());
            echo "Erro ao cadastrar usuário: " . $e->getMessage();
        } catch (Throwable $t) {
            
            error_log("Erro inesperado: " . $t->getMessage());
            echo "Ocorreu um erro inesperado. Tente novamente mais tarde.";
    }
    }

    $erros = $_SESSION['msg_erro'] ?? [];
    $old    = $_SESSION['old_value'] ?? [];
    unset($_SESSION['msg_erro'], $_SESSION['old_value']);
?>

<body>
    <?php 
        foreach ($erros as $key => $value) {
            echo $key .  ": " . $value;
        }
    ?>
    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <input type="text" name="nomeusuario" id="nomeusuario" placeholder="Nome de usuário" value="<?=$old['nomeusuario'] ?? null?>">
        <input type="email" name="email" id="email" placeholder="Email" value="<?=$old['email']?? null?>">
        <input type="date" name="nascimento" id="nascimento" placeholder="Data de nascimento" value="<?=$old['nascimento'] ?? null?>">
        <input type="password" name="senha" id="senha" placeholder="senha" value="<?=$old['senha'] ?? null?>">
        <input type="password" name="senha2" id="senha2" placeholder="Confirme a senha" value="<?=$old['senha2'] ?? null?>">
        <textarea name="bio" id="bio"  value="<?=$old['bio'] ?? null?>"></textarea>
        <input type="submit" value="Registrar">
    </form>
</body>
</html>