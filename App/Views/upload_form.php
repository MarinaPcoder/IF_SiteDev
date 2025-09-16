<?php
// App/Views/upload_form.php
declare(strict_types=1);

session_start();

require_once '../../vendor/autoload.php';

use App\Controllers\UsuarioController;
use App\Controllers\JogoController;

$usuario = new UsuarioController();
$jogo    = new JogoController();

// Constantes de caminho usadas no HTML
const CAMINHO_PUBLIC = './../../public/';
const CAMINHO_INDEX  = './../../public/index.php';

// ----- mensagens de debug (opcional) -----
if (isset($_SESSION['Mensagem_redirecionamento'])) {
    echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
    unset($_SESSION['Mensagem_redirecionamento']);
}

// ----- guard de autenticação/autorizaçao (precisa ser admin) -----
if (empty($_SESSION['Usuario'])) {
    header('Location: ./loginUsuario.php');
    exit;
}
[$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);
if (!$logado || $tipo_usuario !== 'admin') {
    $_SESSION['Mensagem_redirecionamento'] = "Usuario não existe ou não tem permissão. Redirecionado para ./logout.php";
    header('Location: ./logout.php');
    exit;
}

// ----- id do jogo (GET tem prioridade, POST é fallback no submit) -----
$idJogo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idJogo) {
    $idJogo = filter_input(INPUT_POST, 'idJogo', FILTER_VALIDATE_INT);
}
if (!$idJogo) {
    $_SESSION['Mensagem_redirecionamento'] = "Jogo não especificado. Redirecionado para ./../../public";
    header('Location: ./../../public');
    exit;
}

// ----- valida existência do jogo -----
if (!$jogo->ExisteJogo(idJogo: $idJogo)) {
    $_SESSION['Mensagem_redirecionamento'] = "Jogo não encontrado. Redirecionado para ./../../public";
    header('Location: ./../../public');
    exit;
}

// ----- lê dados do jogo -----
$jogoDados = $jogo->LerJogo(idJogo: $idJogo);
if (!$jogoDados) {
    $_SESSION['Mensagem_redirecionamento'] = "Erro ao ler os dados do jogo. Redirecionado para ./../../public";
    header('Location: ./../../public');
    exit;
}

$ContScreenshots = isset($jogoDados['screenshots']) && is_array($jogoDados['screenshots'])
    ? count($jogoDados['screenshots'])
    : 0;

// ----- head -----
$titulo = 'Upload de imagens';
require_once '../../public/assets/components/head.php';

// ----- processamento do POST (upload) -----
$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['Jogo']);

    $poster      = $_FILES['poster']      ?? null;
    $banner      = $_FILES['banner']      ?? null;
    $screenshots = $_FILES['screenshot']  ?? null;

    $erros = $jogo->UploadImagens(
        idJogo: $idJogo,
        poster: $poster,
        banner: $banner,
        screenshots: $screenshots,
        ordemScreenshots: $ContScreenshots
    );

}

// helper de escape curto
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<link rel="stylesheet" href="<?= CAMINHO_PUBLIC . 'assets/css/upform.css' ?>">
<style>
  .erro{background:#2a1e1f;border:1px solid #734040;color:#f7dede;padding:.75rem 1rem;border-radius:.5rem;margin:.5rem 0}
  .red{color:#ff6b6b;margin:.5rem 0}
  .grid-media{display:grid;grid-template-columns:repeat(auto-fill,minmax(420px,1fr));gap:16px;margin:.5rem 0 1rem}
  .media-box{background:#16181d;border:1px solid #2b2f3a;border-radius:.6rem;padding:.75rem}
  .media-box img,.media-box video{width:100%;height:auto;border-radius:.4rem;display:block}
  .media-actions{display:flex;justify-content:flex-end;margin-top:.5rem}
  .media-actions a{background:#2b2f3a;color:#fff;padding:.35rem .6rem;border-radius:.4rem;text-decoration:none}
  .media-actions a:hover{background:#394055}
  .uploader .row{margin:.5rem 0}
  .input-screenshot{display:block}
  .btn{cursor:pointer;background:#3b82f6;border:none;color:#fff;border-radius:.4rem;padding:.45rem .7rem}
  .btn--ghost{background:#2b2f3a}
</style>
</head>

<body>
  <h1>
    <a class="brand__avatar" href="<?= CAMINHO_PUBLIC ?>index.php" aria-label="Storm — Homepage">&lt;</a>
    <?= $h($titulo) . ' de ' . $h($jogoDados['titulo']) ?>
  </h1>

  <?php if (!empty($erros)): ?>
    <?php foreach ($erros as $chave => $msgs): ?>
      <div class="erro">
        <strong><?= $h($chave) ?>:</strong>
        <ul>
          <?php foreach ((array)$msgs as $msg): ?>
            <li><?= $h($msg) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php endforeach ?>
  <?php endif; ?>

  <h2>Poster atual</h2>
  <img
    src="<?= $h(CAMINHO_PUBLIC . ltrim((string)$jogoDados['poster'], '/')) ?>"
    alt="Poster de <?= $h($jogoDados['titulo']) ?>"
    width="200" height="300">
  <?php if (($jogoDados['poster'] ?? '') === "/assets/img/poster.png"): ?>
    <p class="red">Poster padrão, não foi enviada uma imagem personalizada para este jogo.</p>
  <?php endif; ?>

  <h2>Banner atual</h2>
  <img
    src="<?= $h(CAMINHO_PUBLIC . ltrim((string)$jogoDados['banner'], '/')) ?>"
    alt="Banner de <?= $h($jogoDados['titulo']) ?>"
    width="500" height="200">
  <?php if (($jogoDados['banner'] ?? '') === "/assets/img/banner.png"): ?>
    <p class="red">Banner padrão, não foi enviada um banner personalizado para este jogo.</p>
  <?php endif; ?>

  <?php if (!empty($jogoDados['screenshots'])): ?>
    <h2>Screenshots atuais</h2>
    <div class="grid-media">
      <?php foreach ($jogoDados['screenshots'] as $shot): ?>
        <?php
          // Caminho absoluto no filesystem para detecção do MIME
          $abs = realpath(__DIR__ . '/../../public' . $shot['caminho']);
          $mime_type = '';
          if ($abs && extension_loaded('fileinfo')) {
              $finfo = finfo_open(FILEINFO_MIME_TYPE);
              if ($finfo) {
                  $mime_type = (string)finfo_file($finfo, $abs);
                  finfo_close($finfo);
              }
          }
        ?>
        <div class="media-box">
          <?php if (strpos($mime_type, 'video/') === 0): ?>
            <video controls muted playsinline
              src="<?= $h(CAMINHO_PUBLIC . ltrim((string)$shot['caminho'], '/')) ?>">
            </video>
          <?php else: ?>
            <img
              src="<?= $h(CAMINHO_PUBLIC . ltrim((string)$shot['caminho'], '/')) ?>"
              alt="Screenshot de <?= $h($jogoDados['titulo']) ?>">
          <?php endif; ?>
          <div class="media-actions">
            <a href="DeletarImagem.php?id_imagem=<?= $h($shot['id_imagem']) ?>&deletar_imagem=true">Deletar</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="red">Nenhuma screenshot personalizada foi enviada para este jogo.</p>
  <?php endif; ?>

  <form class="uploader" action="<?= $h($_SERVER['PHP_SELF']) ?>?id=<?= $jogoDados['id_jogo']?>" method="post" enctype="multipart/form-data">
    <div class="row">
      <label for="poster">Insira o poster de <?= $h($jogoDados['titulo']) ?>:</label><br>
      <input type="file" name="poster" id="poster" accept="image/*">
    </div>

    <div class="row">
      <label for="banner">Insira um banner de <?= $h($jogoDados['titulo']) ?>:</label><br>
      <input type="file" name="banner" id="banner" accept="image/*">
    </div>

    <input type="hidden" name="idJogo" value="<?= $h($idJogo) ?>">

    <div class="row">
      <label>Fotos/Vídeos para o jogo:</label><br>
      <button type="button" class="btn btn--ghost" onclick="aumentar()">+</button>
      <button type="button" class="btn btn--ghost" onclick="diminuir()">-</button>
    </div>

    <div id="imagens_videos" class="row"></div>

    <div class="row">
      <button type="submit" class="btn">Upload</button>
    </div>
  </form>

  <script>
    let nInputs = 1;
    const MAX_INPUTS = 12;
    const container = document.getElementById('imagens_videos');

    function atualizarInputs() {
      if (nInputs < 0) nInputs = 0;
      if (nInputs > MAX_INPUTS) nInputs = MAX_INPUTS;
      container.innerHTML = '';
      for (let i = 0; i < nInputs; i++) {
        const id = 'screenshot_' + i;
        const wrap = document.createElement('div');
        wrap.style.marginBottom = '8px';
        wrap.innerHTML = `
          <input type="file" name="screenshot[]" id="${id}" accept="image/*,video/*" class="input-screenshot">
        `;
        container.appendChild(wrap);
      }
    }
    function aumentar() { nInputs++; atualizarInputs(); }
    function diminuir() { nInputs--; atualizarInputs(); }

    // inicia com 1 input
    atualizarInputs();
  </script>
</body>
</html>
