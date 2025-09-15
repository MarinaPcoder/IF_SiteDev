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

    const CAMINHO_PUBLIC = './../../public/';
    const CAMINHO_INDEX = './../../public/index.php';
    
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

    $titulo = 'Cadastro de jogos';
    require_once '../../public/assets/components/head.php';
    
?>
 <!-- configuração  Head -->

</head>

<?php 
    

    $erros = [];

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
            

            $erros = $jogo -> Cadastrar(
                titulo: $dados['titulo'], 
                descricao: $dados['descricao'], 
                desenvolvedora: $dados['desenvolvedora'], 
                data_lancamento: $dados['data_lancamento'], 
                link_compra: $dados['link_compra'], 
                plataforma: $dados['plataforma'],
                genero: $dados['genero']
            );

            if (empty($erros)) {

                $_SESSION['Jogo'] = $jogo -> GetJogoPorTituloEPlataforma(titulo: $dados['titulo'], plataforma: $dados['plataforma']);

                header(header: 'Location: ./upload_form.php');
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

    <form action="<?=htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post">
        <input type="text" name="titulo" id="titulo" placeholder="Título do jogo" value="<?=htmlspecialchars($dados['titulo'] ?? '')?>">

        <label for="genero">Gênero:</label>
        <select name="genero" id="genero">
            <option value="7" <?= (($dados['plataforma'] ?? '') == '7') ? 'selected' : '';?>> Ação e combate         </option>
            <option value="4" <?= (($dados['plataforma'] ?? '') == '4') ? 'selected' : '';?>> Esportes e competição  </option>
            <option value="5" <?= (($dados['plataforma'] ?? '') == '5') ? 'selected' : '';?>> Exploração e aventura  </option>
            <option value="6" <?= (($dados['plataforma'] ?? '') == '6') ? 'selected' : '';?>> Música e partygames    </option>
            <option value="2" <?= (($dados['plataforma'] ?? '') == '2') ? 'selected' : '';?>> Plataforma e indie     </option>
            <option value="3" <?= (($dados['plataforma'] ?? '') == '3') ? 'selected' : '';?>> Simulação e construção </option>
            <option value="1" <?= (($dados['plataforma'] ?? '') == '1') ? 'selected' : '';?>> Terror e mistério      </option>
        </select>

        <label for="plataforma">Qual a plataforma do jogo: </label>
        <select name="plataforma" id="plataforma">
            <option value="pc"      <?= (($dados['plataforma'] ?? '') === 'pc')     ? 'selected' : '';?>>PC             </option>
            <option value="ps5"     <?=(($dados['plataforma'] ?? '') == 'ps5')      ? 'selected' : '';?>>Playstation 5  </option>
            <option value="ps4"     <?=(($dados['plataforma'] ?? '') == 'ps4')      ? 'selected' : '';?>>Playstation 4  </option>
            <option value="one"     <?=(($dados['plataforma'] ?? '') == 'one')      ? 'selected' : '';?>>Xbox One       </option>
            <option value="xboxS"   <?=(($dados['plataforma'] ?? '') == 'xboxS')    ? 'selected' : '';?>>Xbox Series S  </option>
            <option value="xboxX"   <?=(($dados['plataforma'] ?? '') == 'xboxX')    ? 'selected' : '';?>>Xbox Series X  </option>
            <option value="switch"  <?=(($dados['plataforma'] ?? '') == 'switch')   ? 'selected' : '';?>>Switch         </option>
        </select>

        <label for="data_lancamento">Data de lançamento: </label>
        <input type="date" name="data_lancamento" id="data_lancamento" value="<?=htmlspecialchars($dados['data_lancamento'] ?? '')?>">

        <input type="text" name="desenvolvedora" id="desenvolvendor" placeholder="Desenvolvedora" value="<?=htmlspecialchars($dados['desenvolvedora'] ?? '')?>">

        <input type="url" name="link_compra" id="compra" placeholder="Link de compra" value="<?=htmlspecialchars($dados['link_compra'] ?? '')?>">
        
        <textarea name="descricao" id="descricao"><?= htmlspecialchars($dados['descricao'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>

        <input type="submit" value="Cadastrar">
    </form>
</body>
</html>