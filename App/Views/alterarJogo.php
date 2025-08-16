<?php 
    session_start();
    use App\Controllers\UsuarioController;

    require_once '../../vendor/autoload.php';
    use App\Controllers\JogoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    } else {
        [$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);

        if (!$logado && $tipo_usuario != 'admin') {
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

    $dadoJogo = ($jogo -> GetJogo(id: $_GET['id'] ?? PaginaInicial()))[0];

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
    
    <form action=" <?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post">
        <input type="text" name="titulo" id="titulo" placeholder="Título do jogo" value="<?=htmlspecialchars(string: $_POST['titulo'] ?? $dadoJogo['titulo'])?>">

        <label for="genero">Gênero:</label>
        <select name="genero" id="genero">
            <option value="7" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['genero']) == '7') ? 'selected' : '';?>>Ação e combate</option>
            <option value="4" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['genero']) == '4') ? 'selected' : '';?>> Esportes e competição</option>
            <option value="5" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['genero']) == '5') ? 'selected' : '';?>> Exploração e aventura</option>
            <option value="6" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['genero']) == '6') ? 'selected' : '';?>> Música e partygames</option>
            <option value="2" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['genero']) == '2') ? 'selected' : '';?>> Plataforma e indie</option>
            <option value="3" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['genero']) == '3') ? 'selected' : '';?>> Simulação e construção </option>
            <option value="1" <?= (htmlspecialchars(string: $_POST['genero'] ?? $dadoJogo['genero']) == '1') ? 'selected' : '';?>> Terror e mistério      </option>
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

        <input type="text" name="desenvolvedora" id="desenvolvendor" placeholder="Desenvolvedora" value="<?= htmlspecialchars(string: $_POST['desenvolvedora'] ?? $dadoJogo['desenvolvedora'])?>">

        <input type="url" name="link_compra" id="compra" placeholder="Link de compra" value="<?= htmlspecialchars(string: $_POST['link_compra'] ?? $dadoJogo['link_compra'])?>">
        
        <textarea name="descricao" id="descricao"><?= htmlspecialchars(string: $_POST['descricao'] ?? $dadoJogo['descricao'], flags: ENT_QUOTES | ENT_SUBSTITUTE, encoding: 'UTF-8') ?></textarea>


        <input type="submit" value="Cadastrar">
    </form>
</body>
</html>