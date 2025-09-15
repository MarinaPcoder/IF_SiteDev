<?php 
    session_start();
    require_once '../../vendor/autoload.php';

    $titulo = 'Login';
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

    use App\Controllers\UsuarioController;
    $usuario = new UsuarioController;
    
?>
</head>

<?php 
    $erros = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $erros = [];
        
        if (isset($_POST['email']) and isset($_POST['senha'])) {
            
            [$email, $errosEmail]   = $usuario -> VerificarEmail(email: $_POST['email']);
            [$senha, $errosSenha]   = $usuario -> VerificarSenha(senha: $_POST['senha'], senha2: $_POST['senha']);

            $erros = array_merge($errosEmail, $errosSenha);

            if (empty($erros)) {
                try {
                    $usuario -> Login(email: $email, senha: $senha);
                } catch (\Exception $e) {
                    
                    $erros[match ($e -> getCode()) {
                        43 => 'Senha',
                        30 => 'Email',
                        default => 'Indefinido',
                    }][] = $e -> getMessage();
                }
                    
            }
            
        }
    }
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

    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <input type="email" name="email" id="email">
        <input type="password" name="senha" id="senha">
        <input type="submit" value="Logar">
    </form>
</body>