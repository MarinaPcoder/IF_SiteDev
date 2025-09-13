<?php 

    session_start();

    require_once '../../vendor/autoload.php';

    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }
    
    use App\Controllers\UsuarioController;
    use App\Controllers\JogoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;

    CONST CAMINHO_INDEX = './../../public/index.php';

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    } else {
        [$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);
    
        if (!$logado) {
            $_SESSION['Mensagem_redirecionamento'] = "Usuario não existe ou não tem permissão. Redirecionado para ./logout.php";
            header(header: "Location: ./logout.php");
            exit;
        }
    }

    $titulo = 'Envio de Sugestão';
    require_once '../../public/assets/components/head.php';
    
?>
 <!-- configuração  Head -->

</head>

<?php 
    $GLOBALS['erros'] = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        var_dump($_POST);

            // Dados do formulário
            $para = "projetostormsugestoes@gmail.com";
            $assunto = $_POST['assunto'];
            $corpo = $_POST['mensagem'] . "

- Enviado por: " . $_SESSION['Usuario']['Email'];

            $headers = "From:projetostormsugestoes@gmail.com" . "\r\n" .
                       "Reply-To: " . $_SESSION['Usuario']['Email'] . "\r\n";

            // Validação simples
            if (empty($assunto)) {
                $GLOBALS['erros']['Assunto'][] = "O campo assunto é obrigatório.";
            }
            if (empty($corpo)) {
                $GLOBALS['erros']['Mensagem'][] = "O campo mensagem é obrigatório.";
            }

            if (empty($GLOBALS['erros'])) {
                // Enviar e-mail
                if (mail($para, $assunto, $corpo, $headers)) {
                    $_SESSION['Mensagem_redirecionamento'] = "E-mail enviado com sucesso.";
                    header("Location: " . CAMINHO_INDEX);
                    exit;
                } else {
                    $_SESSION['Mensagem_redirecionamento'] = "Falha ao enviar o e-mail.";
                    header("Location: " . CAMINHO_INDEX);
                    exit;
                }
            }
    }
?>

<body>
    <?php foreach ($GLOBALS['erros'] as $chave => $msgs): ?>
        <div class="erro">
            <strong><?= $chave ?>:</strong>
            <ul>
                <?php foreach ($msgs as $msg): ?>
                    <li><?= htmlspecialchars(string: $msg, flags: ENT_QUOTES) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endforeach ?>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post">
        <label for="assunto">Assunto:</label>
        <input accept="text" type="text" name="assunto" id="assunto" value="<?= htmlspecialchars(string: $_POST['assunto'] ?? '') ?>">
        <label for="mensagem">Mensagem:</label>
        <textarea name="mensagem" id="mensagem"><?= htmlspecialchars(string: $_POST['mensagem'] ?? '') ?></textarea>
        <input type="submit" value="Enviar">
    </form>