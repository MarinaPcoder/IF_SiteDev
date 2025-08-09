<?php 
    session_start();

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    }

    require_once '../../vendor/autoload.php';

    $titulo = 'Registro de jogos';
    require_once '../../public/assets/components/head.php';
    
?>

</head>

<?php 
    use App\Controllers\JogoController;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $dados = filter_input_array(type: INPUT_POST, options: [
                'titulo'          => FILTER_UNSAFE_RAW,
                'plataforma'      => FILTER_UNSAFE_RAW,
                'data_lancamento' => FILTER_UNSAFE_RAW,
                'desenvolvedora'  => FILTER_UNSAFE_RAW,
                'compra'          => FILTER_UNSAFE_RAW,
                'descricao'       => FILTER_UNSAFE_RAW,
            ], add_empty: true);

        if (isset($dados['titulo'], $dados['plataforma'], $dados['data_lancamento'], $dados['desenvolvedora'], $dados['compra'], $dados['descricao'])) {
            $usuario = new JogoController;

            $erros = $usuario -> Cadastrar(
                titulo: $dados['titulo'], 
                descricao: $dados['descricao'], 
                desenvolvedora: $dados['desenvolvedora'], 
                data_lancamento: $dados['data_lancamento'], 
                link_compra: $dados['compra'], 
                plataforma: $dados['plataforma']
            );

            if (empty($erros)) {
                header(header: 'Location: ../../public/index.php');
                exit;
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
        <input type="text" name="titulo" id="titulo" placeholder="Título do jogo">

        <label for="plataforma">Qual a plataforma do jogo: </label>
        <select name="plataforma" id="plataforma">
            <option value="pc">PC</option>
            <option value="ps5">Playstation 5</option>
            <option value="ps4">Playstation 4</option>
            <option value="one">Xbox One</option>
            <option value="xboxS">Xbox Series S</option>
            <option value="xboxX">Xbox Series X</option>
            <option value="switch">Switch</option>
        </select>

        <label for="data_lancamento">Data de lançamento: </label>
        <input type="date" name="data_lancamento" id="data_lancamento">

        <input type="text" name="desenvolvedora" id="desenvolvendora" placeholder="Desenvolvedora">

        <input type="url" name="compra" id="compra" placeholder="Link de compra">
        
        <textarea name="descricao" id="descricao">
        </textarea>

        <input type="submit" value="Cadastrar">
    </form>
</body>
</html>