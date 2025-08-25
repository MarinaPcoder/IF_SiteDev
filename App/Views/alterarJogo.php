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

        if (!$logado || $tipo_usuario !== 'admin') {
            header(header: 'Location: ./logout.php');
            exit;
        }
    }

    function PaginaInicial(): never {
        header(header: 'Location: ../../public/index.php');
        exit;
    }

    $titulo = 'Alterar jogos';
    require_once '../../public/assets/components/head.php';
    
?>
 <!-- configuração  Head -->

</head>

<?php 

    $erros = [];

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === null || $id === false) PaginaInicial();

    $dadoJogo = $jogo -> GetJogo(id: $id);

    $dadoJogo ? $dadoJogo : PaginaInicial();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dados = filter_input_array(type: INPUT_POST, options: [
                'titulo'          => FILTER_UNSAFE_RAW,
                'plataforma'      => FILTER_UNSAFE_RAW,
                'data_lancamento' => FILTER_UNSAFE_RAW,
                'desenvolvedora'  => FILTER_UNSAFE_RAW,
                'link_compra'     => FILTER_UNSAFE_RAW,
                'descricao'       => FILTER_UNSAFE_RAW,
                'genero'          => FILTER_UNSAFE_RAW
            ], add_empty: true);

        if (isset($dados['titulo'], $dados['plataforma'], $dados['data_lancamento'], $dados['desenvolvedora'], $dados['link_compra'], $dados['descricao'], $dados['genero'])) {
            
            try {
                $erros = $jogo -> Atualizar(
                id: $id,
                titulo: $dados['titulo'], 
                descricao: $dados['descricao'], 
                desenvolvedora: $dados['desenvolvedora'], 
                data_lancamento: $dados['data_lancamento'], 
                link_compra: $dados['link_compra'], 
                plataforma: $dados['plataforma'],
                genero: (int) $dados['genero']
                );
                
            } catch (\PDOException $th) {
                print $th->getCode() . ": ". $th ->getMessage();
                exit;
            }
            
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
    
    <form action="<?= htmlspecialchars(string: $_SERVER['PHP_SELF'], flags: ENT_QUOTES | ENT_SUBSTITUTE, encoding: 'UTF-8').'?id='.(int)$id ?>" method="post">
        <input type="text" name="titulo" id="titulo" placeholder="Título do jogo" value="<?=htmlspecialchars(string: $_POST['titulo'] ?? $dadoJogo['titulo'])?>">

        <label for="genero">Gênero:</label>
        <select name="genero" id="genero">
            <option value="7" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['generos']['id_genero']) == '7') ? 'selected' : '';?>>Ação e combate</option>
            <option value="4" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['generos']['id_genero']) == '4') ? 'selected' : '';?>> Esportes e competição</option>
            <option value="5" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['generos']['id_genero']) == '5') ? 'selected' : '';?>> Exploração e aventura</option>
            <option value="6" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['generos']['id_genero']) == '6') ? 'selected' : '';?>> Música e partygames</option>
            <option value="2" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['generos']['id_genero']) == '2') ? 'selected' : '';?>> Plataforma e indie</option>
            <option value="3" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['generos']['id_genero']) == '3') ? 'selected' : '';?>> Simulação e construção </option>
            <option value="1" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['generos']['id_genero']) == '1') ? 'selected' : '';?>> Terror e mistério      </option>
        </select>

        <label for="plataforma">Qual a plataforma do jogo: </label>
        
        <select name="plataforma" id="plataforma">
            <option value="pc" <?= (htmlspecialchars(string: $_POST['plataforma'] ?? $dadoJogo['plataforma']) == 'pc') ? 'selected' : '';?> >PC</option>
            <option value="ps5" <?=(htmlspecialchars(string: $_POST['plataforma'] ?? $dadoJogo['plataforma']) == 'ps5') ? 'selected' : '';?>>Playstation 5</option>
            <option value="ps4" <?=(htmlspecialchars(string: $_POST['plataforma'] ?? $dadoJogo['plataforma']) == 'ps4') ? 'selected' : '';?>>Playstation 4</option>
            <option value="one" <?=(htmlspecialchars(string: $_POST['plataforma'] ?? $dadoJogo['plataforma']) == 'one') ? 'selected' : '';?>>Xbox One </option>
            <option value="xboxS" <?=(htmlspecialchars(string: $_POST['plataforma'] ?? $dadoJogo['plataforma']) == 'xboxS') ? 'selected' : '';?>>Xbox Series S </option>
            <option value="xboxX" <?=(htmlspecialchars(string: $_POST['plataforma'] ?? $dadoJogo['plataforma']) == 'xboxX') ? 'selected' : '';?>>Xbox Series X </option>
            <option value="switch" <?=(htmlspecialchars(string: $_POST['plataforma'] ?? $dadoJogo['plataforma']) == 'switch') ? 'selected' : '';?>>Switch </option>
        </select>

        <label for="data_lancamento">Data de lançamento: </label>
        <input type="date" name="data_lancamento" id="data_lancamento" value="<?= htmlspecialchars(string: $_POST['data_lancamento'] ?? $dadoJogo['data_lancamento'])?>">

        <input type="text" name="desenvolvedora" id="desenvolvendora" placeholder="Desenvolvedora" value="<?= htmlspecialchars(string: $_POST['desenvolvedora'] ?? $dadoJogo['desenvolvedora'])?>">

        <input type="url" name="link_compra" id="compra" placeholder="Link de compra" value="<?= htmlspecialchars(string: $_POST['link_compra'] ?? $dadoJogo['link_compra'])?>">
    
        <textarea name="descricao" id="descricao"><?= htmlspecialchars(string: $_POST['descricao'] ?? $dadoJogo['descricao'], flags: ENT_QUOTES | ENT_SUBSTITUTE, encoding: 'UTF-8') ?></textarea>


        <input type="submit" value="Alterar">
    </form>
</body>
</html>