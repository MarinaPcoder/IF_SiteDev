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

    if (isset($_SESSION['Jogo'])) {
        $jogoDados = $_SESSION['Jogo'];
        unset($_SESSION['Jogo']);

        $idJogo = $jogoDados['Id'];
    }

    if(isset($_GET['id'])) {
        $idJogo = $_GET['id'];
    }

    if (isset($_POST['idJogo'])) {
        $idJogo = $_POST['idJogo'];
    }

    if (!isset($idJogo)) {
        header(header: 'Location: ./../../public');
        exit;
    }

    if (!$jogo->ExisteJogo(idJogo: $idJogo)) {
        header(header: 'Location: ./../../public');
        exit;
    }

    $jogoDados = $jogo->LerJogo(idJogo: $idJogo);
    if (!$jogoDados) {
        header(header: 'Location: ./../../public');
        exit;
    }

    $titulo = 'Upload de imagens';
    require_once '../../public/assets/components/head.php';

    $erros = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $logo = $_FILES['logo'] ?? null;
        $banner = $_FILES['banner'] ?? null;
        $screenshots = $_FILES['screenshot'] ?? null;

        $erros = $jogo->UploadImagens(
            idJogo: $idJogo,
            logo: $logo,
            banner: $banner,
            screenshots: $screenshots
        );
    }
?>
 <!-- configuração  Head -->
<style>
    .red {
        color: red;
        font-weight: bold;
        text-decoration: underline;
        font-size: 15px;
    }
</style>
</head>

<body>
    <h1><?= htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').' de '.$jogoDados['titulo'] ?></h1>

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
    <H2>Logo atual</H2>
    
    <img src="<?= htmlspecialchars('./../../public/assets/'.$jogoDados['logo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Logo de <?= htmlspecialchars($jogoDados['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" srcset="" width="100" height="100">
    <?php 
        if ($jogoDados['logo'] == "img/logo.png") {
            echo "<p class='red'>Logo padrão, não foi enviada uma logo personalizada para este jogo.</p>";
        }
    ?>

    <H2>Banner atual</H2>
    <img src="<?= htmlspecialchars('./../../public/assets/'.$jogoDados['banner'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Banner de <?= htmlspecialchars($jogoDados['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" srcset="" width="600" height="200">
    <?php 
        if ($jogoDados['banner'] == "img/banner.png") {
            echo "<p class='red'>Banner padrão, não foi enviada uma banner personalizada para este jogo.</p>";
        }
    ?>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post" enctype="multipart/form-data">
        
        <label for="logo">Insira a logo de <?= htmlspecialchars($jogoDados['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>:</label>
        <br>
        <input type="file" name="logo" id="logo" accept="image/*">
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
            Idteste.innerHTML = ''
            for (let i = 0; i < Ninputs; i++) {
                Idteste.innerHTML += '<input type="file" name="screenshot[]" id="screenshot" accept="image/*,video/*"> <br><br>'
            }
        }
    </script>
</body>
</html>