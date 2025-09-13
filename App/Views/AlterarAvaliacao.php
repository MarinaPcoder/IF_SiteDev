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

    $titulo = 'Avaliações - Alterar';
    require_once '../../public/assets/components/head.php';
    
?>
 <!-- configuração  Head -->

</head>

<?php 
    $GLOBALS['erros'] = [];

        // Verifica se veio o id da avaliação
            $id_avaliacao = $_GET['id'] ?? ($_SESSION['avaliacao']['alterar']['id'] ?? null);
            if ($id_avaliacao === null || !ctype_digit((string)$id_avaliacao)) {
                $_SESSION['Mensagem_redirecionamento'] = "ID da avaliação inválido.";
                header('Location: ' . CAMINHO_INDEX);
                exit;
            }

            $id_avaliacao = (int) $id_avaliacao;

            $dadoAvaliacao = $avaliacao->Ler(id: $id_avaliacao);

            if (empty($dadoAvaliacao)) {
                $_SESSION['Mensagem_redirecionamento'] = "Avaliação não encontrada.";
                header(header: "Location: " . CAMINHO_INDEX);
                exit;
            } else {
                $_SESSION['avaliacao']['alterar']['id'] = $id_avaliacao;  
            }

        // Verifica se o usuário tem permissão para alterar a avaliação
            if ($dadoAvaliacao[0]['id_usuario'] !== $_SESSION['Usuario']['Id'] && $tipo_usuario !== 'admin') {
                $_SESSION['Mensagem_redirecionamento'] = "Você não tem permissão para alterar esta avaliação.";
                header(header: "Location: " . CAMINHO_INDEX);
                exit;
            }
        
        // Ler dados do usuário relacionado
            $dadoUsuario = $usuario->getUsuario(id: $dadoAvaliacao[0]['id_usuario']);
            if (empty($dadoUsuario)) {
                $_SESSION['Mensagem_redirecionamento'] = "Usuário relacionado à avaliação não encontrado.";
                header(header: "Location: " . CAMINHO_INDEX);
                exit;
            }

        // Ler dados do jogo relacionado
            $dadoJogo = $jogo->LerJogo(idJogo: $dadoAvaliacao[0]['id_jogo']);
            if (empty($dadoJogo)) {
                $_SESSION['Mensagem_redirecionamento'] = "Jogo relacionado à avaliação não encontrado.";
                header(header: "Location: " . CAMINHO_INDEX);
                exit;
            }

    // Processa o formulário quando enviado 
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dados = filter_input_array(type: INPUT_POST, options: [
            'nota'          => FILTER_UNSAFE_RAW,
            'justificativa' => FILTER_UNSAFE_RAW,
        ], add_empty: true);

        if ($dados['nota'] == null) {
            $GLOBALS['erros']['Nota'][] = "Nota é obrigatória.";
        }

        if ($dados['justificativa'] == null) {
            $GLOBALS['erros']['Justificativa'][] = "Justificativa é obrigatória.";
        }

        if (empty($GLOBALS['erros'])) {
            try {
                
                $sucesso = $avaliacao->Atualizar(
                    id:              $id_avaliacao,
                    nota:            $dados['nota'],
                    justificativa:   $dados['justificativa']
                );

                if (empty($GLOBALS['erros']) and $sucesso) {
                    
                    $_SESSION['Mensagem_redirecionamento'] = "Avaliação atualizada com sucesso.";
                    unset($_SESSION['avaliacao']);

                    header(header: "Location: " . CAMINHO_INDEX);
                    exit;
                }
                
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

    <h1>Alterar Avaliação de <?= htmlspecialchars(string: $dadoUsuario[0]['nome_usuario'] ?? '') ?> para o jogo <?= htmlspecialchars(string: $dadoJogo['titulo'] ?? '') ?></h1>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post">
        <label for="nota">nota:</label>
        <input type="number" name="nota" id="nota" min="0" max="10" required value="<?= htmlspecialchars(string: $dadoAvaliacao[0]['nota'] ?? '') ?>">
        <label for="justificativa">Justificativa:</label>
        <input type="text" name="justificativa" id="justificativa" required value="<?= htmlspecialchars(string: $dadoAvaliacao[0]['justificativa'] ?? '') ?>">
        <input type="submit" value="Enviar">
    </form>