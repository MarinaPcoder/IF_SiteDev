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

    $titulo = 'Exclusão de conta';
    require_once '../../public/assets/components/head.php';
    
?>
<!-- configuração  Head -->
<link rel="stylesheet" href="<?=CAMINHO_PUBLIC . 'assets/css/delusuario.css'?>">

</head>

<?php 

    $dado = ($usuario -> getUsuario(id: $_SESSION['Usuario']['Id']))[0];

    $erros = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
         try {
            [$senha, $ErrosSenha] = $usuario ->VerificarSenha($_POST['senha'], $_POST['senha']);
            
            if (!empty($ErrosSenha)) {
                $erros['Formato de senha'] = $ErrosSenha;
            }

            $usuario->ExcluirUsuario($dado['id_usuario'], $senha);

         } catch(\Exception $e){
            $erros[match ($e -> getCode()) {
                        43 => 'Senha',  
                        default => 'Indefinido',
                    }]
                    
                    [] = $e -> getMessage();
         } catch (\PDOException $th) {
            error_log(message: "Erro PDO: " . $e->getMessage());

            $erros['Indefinido'][] = "Erro ao deletar o usuário: " . $e->getMessage();
            
         } catch (Throwable $t) {
            error_log(message: "Erro inesperado: " . $t->getMessage());
            echo "Ocorreu um erro inesperado. Tente novamente mais tarde. Erro: " . $t->getMessage();
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
        <p>Você tem certeza que deseja excluir a conta de <?=$dado['nome_usuario']?>?</p>
        <input placeholder="Insira a sua senha:" type="password" name="senha" id="senha">
        <input type="submit"  value="Sim">
    </form>
    <button type="button" onclick="redirecionar()">Não</button>

    <script>
        function redirecionar() {
            window.location.href = "./Show.php";
        }
    </script>

</body>
</html>