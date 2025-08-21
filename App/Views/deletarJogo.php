<?php 
    session_start();

    require_once '../../vendor/autoload.php';
    use App\Controllers\UsuarioController;
    use App\Controllers\JogoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;
    
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
    $id = (int) $_GET['id'] ?? PaginaInicial();

    $id ? : PaginaInicial();

    $dadoJogo = $jogo -> GetJogo(id: $id);

    $dadoJogo ? : PaginaInicial();


    $plataforma = match ($dadoJogo['plataforma']) {
        'pc' => 'PC',
        'ps5' => 'Playstation 5',
        'ps4' => 'Playstation 5',
        'one' => 'Xbox One',
        'xboxS' => 'Xbox Series S',
        'xboxX' => 'Xbox Series X',
        'switch' => 'Nintendo Switch',
    };

    $erros = [];

    $id = (int) $_GET['id'] ?? PaginaInicial();
    $id ? : PaginaInicial();

    $dadoJogo = $jogo -> GetJogo(id: $id);

    $dadoJogo ? : PaginaInicial();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            [$senha, $ErrosSenha] = $usuario ->VerificarSenha($_POST['senha'], $_POST['senha']);

            if(!empty($ErrosSenha)) {
                $erros['Formato de senha'] = $ErrosSenha;
            }
            
        } catch (\Throwable $th) {
            $erros[][] = '';
        }
    }

?>
<body>
    
    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <p>Você tem certeza que deseja excluir a versão de <?=$plataforma?> do jogo <?=$dadoJogo['titulo']?>?</p>
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