<?php 
    session_start();

    require_once '../../vendor/autoload.php';

    use App\Controllers\UsuarioController;
    $usuario = new UsuarioController;

    const CAMINHO_PUBLIC = './../../public/';
    const CAMINHO_INDEX = './../../public/index.php';

    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    } else {
        [$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);
        
        if (!$logado) {
            header(header: 'Location: ./logout.php');
            exit;
        }
    }

    $titulo = 'Edição';
    require_once '../../public/assets/components/head.php';
?>
 <!-- configuração  Head -->

</head>

<?php 

    $dado = ($usuario -> getUsuario(id: $_SESSION['Usuario']['Id']))[0];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        try {
            $usuario -> AtualizarUsuario(
                id: $_SESSION['Usuario']['Id'],
                nome: $_POST['nomeusuario'], 
                email: $_POST['email'], 
                datadenascimento: $_POST['nascimento'], 
                senha: $_POST['senha'], 
                senha2: $_POST['senha2'], 
                bio: $_POST['bio']
            );
        } catch (PDOException $e) {
            
            error_log(message: "Erro PDO: " . $e->getMessage());

            switch ($e->getCode()) {
                case 23000:
                    $erros['Email'][] = 'E-mail já está registrado.';
                    break;
                
                default:
                    $erros['Indefinido'][] = "Erro ao cadastrar usuário: " . $e->getMessage();
                    break;
            }
        } catch (Throwable $t) {
            
            error_log(message: "Erro inesperado: " . $t->getMessage());
            $erros['Indefinido'][] = "Ocorreu um erro inesperado. Tente novamente mais tarde. " . $t->getMessage();
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
        <input type="text" name="nomeusuario" id="nomeusuario" placeholder="Nome de usuário" value="<?=htmlspecialchars($_POST['nomeusuario']  ?? $dado["nome_usuario"] )?>">

        <input type="email" name="email" id="email" placeholder="Email" value="<?=htmlspecialchars($_POST['email']  ?? $dado['email'] )?>">

        <input type="date" name="nascimento" id="nascimento" placeholder="Data de nascimento" value="<?=htmlspecialchars($_POST['nascimento'] ?? $dado['data_nascimento']  )?>">

        <input type="password" name="senha" id="senha" placeholder="Nova senha" value="<?=htmlspecialchars($_POST['senha']  ?? null)?>">

        <input type="password" name="senha2" id="senha2" placeholder="Confirme a nova senha" value="<?=htmlspecialchars(string: $_POST['senha2'] ?? null)?>">
 
        <textarea name="bio" id="bio"><?= htmlspecialchars(string: $_POST['bio'] ?? $dado['bio'], flags: ENT_QUOTES) ?></textarea>

        <input type="submit" value="Alterar">
    </form>
</body>
</html>