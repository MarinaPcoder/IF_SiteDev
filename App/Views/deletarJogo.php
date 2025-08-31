<?php 
    session_start();

    require_once '../../vendor/autoload.php';
    use App\Controllers\UsuarioController;
    use App\Controllers\JogoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;
    
    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    } else {
        [$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);
        if (!$logado || $tipo_usuario !== 'admin') 
            {
            header(header: 'Location: ./logout.php');
            exit;
        }
    }

    function PaginaInicial(): never {
        header(header: 'Location: ../../public/index.php');
        exit;
    }

    $titulo = 'Exclusão de Jogo';
    require_once '../../public/assets/components/head.php';
    
?>
<!-- configuração  Head -->

</head>

<?php 
    $erros = [];

    $id = filter_input(type: INPUT_GET, var_name: 'id', filter: FILTER_VALIDATE_INT);
    if ($id === null || $id === false) PaginaInicial();

    $dadoJogo = $jogo -> GetJogo(id: $id);

    $dadoJogo ? $dadoJogo : PaginaInicial();

    $erros = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            [$senha, $ErrosSenha] = $usuario ->VerificarSenha($_POST['senha'], $_POST['senha']);

            if(empty($ErrosSenha)) {
                $jogo -> Deletar(id: $id, id_usuario: $_SESSION['Usuario']['Id'], senhaForm: $senha);
            } else {
                $erros['Formato de senha'] = $ErrosSenha;
            }
            
        } catch (\Throwable $th) {
            $erros[match ($th -> getCode()) {
                        1 => 'Senha',  
                        default => 'Indefinido',
                    }]
                    
                    [] = $th -> getMessage();
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
    
    <form action="<?= htmlspecialchars(string: $_SERVER['PHP_SELF'], flags: ENT_QUOTES | ENT_SUBSTITUTE, encoding: 'UTF-8').'?id='.(int)$id ?>" method="post">
        <p>Você tem certeza que deseja excluir a versão de <?=$dadoJogo['plataforma']?> do jogo <?=$dadoJogo['titulo']?>?</p>
        <input placeholder="Insira a sua senha:" type="password" name="senha" id="senha">
        <input type="submit"  value="Sim">
    </form>
    <button type="button" onclick="redirecionar()">Não</button>

    <script>
        function redirecionar() {
            window.location.href = "../../public/";
        }
    </script>

</body>
</html>