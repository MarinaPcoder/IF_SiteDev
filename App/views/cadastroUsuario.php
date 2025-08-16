<?php 
    session_start();

    require_once '../../vendor/autoload.php';

    $titulo = 'Registro';
    require_once '../../public/assets/components/head.php';
    

    if (isset($_SESSION['Usuario'])) {
        header(header: 'Location: ../../public/index.php');
        exit;
    }
?>
<!-- configuração  Head -->

</head>

<?php 
    use App\Controllers\UsuarioController;

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
            
        } catch (PDOException $e) {
            
            error_log(message: "Erro PDO: " . $e->getMessage());

            if ($e->getCode() == 23000) {
                $_SESSION['msg_erro']['Email'][] = 'Não é possível cadastrar o usuário: E-mail já está registrado';
            } else {
                $_SESSION['msg_erro']['Indefinido'][] = "Erro ao cadastrar usuário: " . $e->getMessage();
            }

        } catch (Throwable $t) {
            error_log(message: "Erro inesperado: " . $t->getMessage());
            echo "Ocorreu um erro inesperado. Tente novamente mais tarde." . $t->getMessage();
    }
    }

    $erros = $_SESSION['msg_erro'] ?? [];
    
    unset($_SESSION['msg_erro']);
?>

<body>
        <?php foreach ($erros as $chave => $msgs): ?>
            <div class="erro">
                <strong><?= $chave ?>:</strong>
                <ul>
                    <?php foreach ($msgs as $msg): ?>
                        <li><?= htmlspecialchars(string: $msg, flags: ENT_QUOTES) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endforeach ?>
    <form action="<?= htmlspecialchars(string: $_SERVER['PHP_SELF'], flags: ENT_QUOTES) ?>" method="post">
        <input type="text" name="nomeusuario" id="nomeusuario" placeholder="Nome de usuário" value="<?=htmlspecialchars(string: $_POST['nomeusuario']  ?? null )?>">

        <input type="email" name="email" id="email" placeholder="Email" value="<?=htmlspecialchars(string: $_POST['email'] ?? null)?>">

        <input type="date" name="nascimento" id="nascimento" placeholder="Data de nascimento" value="<?=htmlspecialchars(string: $_POST['nascimento'] ?? null )?>">

        <input type="password" name="senha" id="senha" placeholder="senha" value="<?=$_POST['senha'] ?? null?>">

        <input type="password" name="senha2" id="senha2" placeholder="Confirme a senha" value="<?=htmlspecialchars(string: $_POST['senha2'] ?? null)?>">
 
        <textarea name="bio" id="bio"><?= htmlspecialchars(string: $_POST['bio'] ?? '', flags: ENT_QUOTES) ?></textarea>

        <input type="submit" value="Registrar">
    </form>
</body>
</html>