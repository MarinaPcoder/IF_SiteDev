<?php 
    session_start();

    require_once '../../vendor/autoload.php';
    use App\Controllers\UsuarioController;
    $usuario = new UsuarioController();

    $titulo = 'Registro';
    require_once '../../public/assets/components/head.php';

    const CAMINHO_PUBLIC = './../../public/';
    const CAMINHO_INDEX = './../../public/index.php';

    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }    

    if (isset($_SESSION['Usuario'])) {
        header(header: 'Location: ../../public/index.php');
        exit;
    }
?>
    <!-- configuração  Head -->

</head>

<?php 

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        try {
            $usuario ->cadastrar(
                nome: $_POST['nomeusuario'], 
                email: $_POST['email'], 
                datadenascimento: $_POST['nascimento'], 
                senha: $_POST['senha'], 
                senha2: $_POST['senha2'], 
                bio: $_POST['bio']
            );
            
        } catch (PDOException $e) {

            // Trata erros do PDO
            
            error_log(message: "Erro PDO: " . $e->getMessage());

            switch ($e->getCode()) {
                case 23000:
                    $erros['Email'][] = 'Não é possível cadastrar o usuário: E-mail já está registrado';
                    break;
                
                default:
                    $erros['Indefinido'][] = "Erro ao cadastrar usuário: " . $e->getMessage();
                    break;
            }

        } catch (Throwable $t) {

            // Trata erros do controller

            switch ($t->getCode()) {
                case 'value':
                    # code...
                    break;
                
                default:
                    $erros['Indefinido'][] = "Ocorreu um erro inesperado. Tente novamente mais tarde." . $t->getMessage();
                    break;
            }
        }
    }

    $erros = array_merge($erros ?? [], $GLOBALS['msg_erro'] ?? []);
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