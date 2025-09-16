<?php 
    session_start();

    require_once '../../vendor/autoload.php';

    use App\Controllers\UsuarioController;
    use App\Controllers\JogoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;

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
    
        if (!$logado || $tipo_usuario !== 'admin') {

        $_SESSION['Mensagem_redirecionamento'] = "Usuario não existe ou não tem permissão. Redirecionado para ./logout.php";

            header(header: 'Location: ./logout.php');
            exit;
        }
    }

    if (isset($_SESSION['Jogo'])) {
        $idJogo = $_SESSION['Jogo']['id_jogo'];
    }

    if(isset($_GET['id'])) {
        $idJogo = $_GET['id'];
    }

    if (isset($_POST['idJogo'])) {
        $idJogo = $_POST['idJogo'];
    }

    if (!isset($idJogo)) {
        $_SESSION['Mensagem_redirecionamento'] = "Jogo não especificado. Redirecionado para ./../../public";

        header(header: 'Location: ./../../public');
        exit;
    }

    if (!$jogo->ExisteJogo(idJogo: $idJogo)) {
        $_SESSION['Mensagem_redirecionamento'] = "Jogo não encontrado. Redirecionado para ./../../public";
        header(header: 'Location: ./../../public');
        exit;
    }

    $jogoDados = $jogo->LerJogo(idJogo: $idJogo);
    if (!$jogoDados) {
        $_SESSION['Mensagem_redirecionamento'] = "Erro ao ler os dados do jogo. Redirecionado para ./../../public";
        header(header: 'Location: ./../../public');
        exit;
    }

    $ContScreenshots = count($jogoDados['screenshots']);

    $titulo = 'Upload de imagens';
    require_once '../../public/assets/components/head.php';

    $erros = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        unset($_SESSION['Jogo']);
        
        $poster = $_FILES['poster'] ?? null;
        $banner = $_FILES['banner'] ?? null;
        $screenshots = $_FILES['screenshot'] ?? null;

        $erros = $jogo->UploadImagens(
            idJogo: $idJogo,
            poster: $poster,
            banner: $banner,
            screenshots: $screenshots,
            ordemScreenshots: $ContScreenshots
        );

        if (empty($erros)) {
            $_SESSION['Jogo'] = $jogo->GetJogoPorTituloEPlataforma(titulo: $jogoDados['titulo'], plataforma: $jogoDados['plataforma']);

            $message = "Imagens enviadas com sucesso.";
            echo "<script>console.log('PHP Debug: " . addslashes($message) . "');</script>";

            header(header: 'Location: ./upload_form.php');
            exit;
        }
    }


?>
 <!-- configuração  Head -->

<link rel="stylesheet" href="<?=CAMINHO_PUBLIC . 'assets/css/upform.css'?>">
    <style>
        
    </style>
</head>

<body>
        
    <h1><a class="brand__avatar" href="<?=CAMINHO_PUBLIC?>index.php" aria-label="Storm — Homepage"><</a> <?= htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').' de '.$jogoDados['titulo'] ?></h1>

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
    <H2>Poster atual</H2>

    <img src="<?= htmlspecialchars('./../../public'.$jogoDados['poster'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Poster de <?= htmlspecialchars($jogoDados['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" srcset="" width="200" height="300">
    <?php 
        if ($jogoDados['poster'] == "/assets/img/poster.png") {
            echo "<p class='red'>Poster padrão, não foi enviada uma imagem personalizada para este jogo.</p>";
        }
    ?>

    <H2>Banner atual</H2>
    <img src="<?= htmlspecialchars('./../../public'.$jogoDados['banner'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Banner de <?= htmlspecialchars($jogoDados['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" srcset="" width="500" height="200">
    <?php 
        if ($jogoDados['banner'] == "/assets/img/banner.png") {
            echo "<p class='red'>Banner padrão, não foi enviada uma banner personalizada para este jogo.</p>";
        }
    ?>

    <?php if (!empty($jogoDados['screenshots'])): ?>
        <H2>Screenshots atuais</H2>
    <?php endif; ?>

    <?php foreach ($jogoDados['screenshots'] as $screenshot): ?>
        <div>
            
            <img src="<?= htmlspecialchars('./../../public'.$screenshot['caminho'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Screenshot de <?= htmlspecialchars($jogoDados['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" srcset="" width="400" height="200">
            <?php 
                if (empty($jogoDados['screenshots'])) {
                    echo "<p class='red'>Nenhuma screenshot personalizada foi enviada para este jogo.</p>";
                }
            ?>
            <a href="DeletarImagem.php?id_imagem=<?=htmlspecialchars($screenshot['id_imagem'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>&deletar_imagem=true">Deletar</a>
        </div>
    <?php endforeach?>
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post" enctype="multipart/form-data">

        <label for="poster">Insira o poster de <?= htmlspecialchars($jogoDados['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>:</label>
        <br>
        <input type="file" name="poster" id="poster" accept="image/*">
        <br>
        <br>
        <label for="banner">Insira um banner de <?= htmlspecialchars($jogoDados['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>:</label>
        <br>
        <input type="file" name="banner" id="banner" accept="image/*">
        <br>
        <input type="hidden" name="idJogo" value="<?= htmlspecialchars(string: $idJogo, flags: ENT_QUOTES | ENT_SUBSTITUTE, encoding: 'UTF-8') ?>">
        <br>
        <label for="banner">Fotos/vídeos para o jogo:</label>
        <br>
            <button type="button" onclick="aumentar()">+</button>
            <button type="button" onclick="diminuir()">-</button>
        <br>
        <br>
        
        <div id="imagens/videos">
            <!-- <input type="file" name="screenshot[]" id="screenshot" accept="image/*"> -->
        </div>

        <br>
        <input type="submit" value="Upload">
    </form>

    <script>
        Ninputs = 1
        Idteste = document.getElementById('imagens/videos')
        AtualizarInputsFotos()
        
        function aumentar() {
            Ninputs++
            AtualizarInputsFotos()
        }

        function diminuir() {
            Ninputs--

            AtualizarInputsFotos()
        }

        function AtualizarInputsFotos() {
            Idteste.innerHTML = '';
            for (let i = 0; i < Ninputs; i++) {
                Idteste.innerHTML += `
                    <input type="file" name="screenshot[]" id="screenshot" accept="image/*,video/*" class="input-screenshot">
                    <br><br>`;
            }
        }
    </script>
</body>
</html>