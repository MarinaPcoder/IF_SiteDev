<?php 
    session_start();
    
    require_once '../../vendor/autoload.php';
    use App\Controllers\UsuarioController;
    use App\Controllers\JogoController;
    use App\Controllers\AvaliacaoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;
    $avaliacao = new AvaliacaoController;

    CONST CAMINHO_INDEX = './../../public/index.php';

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

    $titulo = 'Avaliações';
    require_once '../../public/assets/components/head.php';
    
?>
 <!-- configuração  Head -->

</head>

<?php 
    $GLOBALS['erros'] = [];

    // Id do Usuario
        $id_usuario = (int) $_SESSION['Usuario']['Id'] ?? null;

        if (isset($id_usuario) and is_int($id_usuario)) {
            $_SESSION['avaliacao']['cadastro']['id_usuario'] = $id_usuario;
        } else {
            $_SESSION['Mensagem_redirecionamento'] = "ID do usuário inválido.";
            header(header: "Location: " . CAMINHO_INDEX);
        }

    // Id do Jogo
        $id_jogo = (int) ($_GET['id_jogo'] ?? $_SESSION['avaliacao']['cadastro']['id_jogo'] ?? null) ?? null;

        if (isset($id_jogo) and is_int($id_jogo) and $jogo->ExisteJogo(idJogo: $id_jogo) != False) {
            $_SESSION['avaliacao']['cadastro']['id_jogo'] = $id_jogo;
        } else {
            $_SESSION['Mensagem_redirecionamento'] = "ID do jogo inválido. " . $_GET['id_jogo'];
            header(header: "Location: " . CAMINHO_INDEX);
            }

    // Verifica se a avaliação já existe
        if ($avaliacao->AvaliacaoExiste(id_usuario: $id_usuario, id_jogo: $id_jogo)) {
            $avaliacaoExistente = $avaliacao->LerPorUsuarioEJogo(id_usuario: $id_usuario, id_jogo: $id_jogo);
            $_SESSION['Mensagem_redirecionamento'] = "Você já avaliou este jogo.";
            header(header: "Location: AlterarAvaliacao.php?id=" . $avaliacaoExistente[0]['id_avaliacao']);
            exit;
        }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $id_usuario = $_SESSION['avaliacao']['cadastro']['id_usuario'];
        unset($_SESSION['avaliacao']['cadastro']['id_usuario']);

        $id_jogo = $_SESSION['avaliacao']['cadastro']['id_jogo'];
        unset($_SESSION['avaliacao']['cadastro']['id_jogo']);

        $dados = filter_input_array(type: INPUT_POST, options: [
            'nota'          => FILTER_UNSAFE_RAW,
            'justificativa' => FILTER_UNSAFE_RAW,
        ], add_empty: true);

        if ($dados['nota'] === null) {
            $GLOBALS['erros']['Nota'][] = "Nota é obrigatória.";
        }

        if ($dados['justificativa'] === null) {
            $GLOBALS['erros']['Justificativa'][] = "Justificativa é obrigatória.";
        }

        if (empty($GLOBALS['erros'])) {
            try {
                $avaliacao->Cadastrar($id_usuario, $id_jogo, $dados['nota'], $dados['justificativa']);

                unset($_SESSION['avaliacao']);
                $_SESSION['Mensagem_redirecionamento'] = "Avaliação cadastrada com sucesso.";
                header(header: "Location: " . CAMINHO_INDEX);
                exit;
            } catch (\Throwable $th) {
                $GLOBALS['erros']['Avaliação'][] = "Erro ao cadastrar avaliação: " . $th->getMessage() . " Código: " . $th->getCode() . " Arquivo: " . $th->getFile() . " Linha: " . $th->getLine();
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
        <label for="nota">Nota:</label>
        <input type="number" name="nota" id="nota" min="0" max="10" value="<?= htmlspecialchars(string: $_POST['nota'] ?? '') ?>">
        <label for="justificativa">Justificativa:</label>
        <input type="text" name="justificativa" id="justificativa" value="<?= htmlspecialchars(string: $_POST['justificativa'] ?? '') ?>">
        <input type="submit" value="Enviar">
    </form>