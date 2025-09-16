<?php
// public/aval-jogo.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\DB\Conexao;

const CAMINHO_VIEWS = './../App/Views/';

// ----------------- valida id -----------------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header('Location: index.php');
  exit;
}

$pdo = Conexao::getInstancia();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// helper pra montar caminho web respeitando subpasta
$basePath   = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$toWeb = function (?string $p) use ($basePath): string {
  $p = (string)($p ?? '');
  if ($p === '') return '';
  if (preg_match('#^https?://#i', $p)) return $p;         // url absoluta
  $p = ltrim($p, '/');                                    // ex: uploads/...
  // se app está na raiz "/", não duplica a barra
  $prefix = $basePath ? $basePath.'/' : '/';
  return $prefix . $p;
};

// ----------------- ações POST (nova avaliação / deletar a própria) -----------------
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action  = $_POST['action'] ?? '';
  $userId  = (int)($_SESSION['Usuario']['Id'] ?? 0);

  try {
    if ($action === 'new_review') {
      if (!$userId) {
        $flash = 'Faça login para avaliar.';
      } else {
        $nota = isset($_POST['nota']) ? (float)$_POST['nota'] : -1;
        $just = trim((string)($_POST['just'] ?? ''));
        if ($nota < 0 || $nota > 10) {
          $flash = 'Nota inválida (0–10).';
        } elseif (mb_strlen($just) < 3) {
          $flash = 'Escreva um comentário (mín. 3 caracteres).';
        } else {
          // se já existe avaliação do usuário para o jogo, atualiza; senão, insere
          $stmt = $pdo->prepare("SELECT id_avaliacao FROM Avaliacao WHERE id_usuario = :u AND id_jogo = :j LIMIT 1");
          $stmt->execute([':u'=>$userId, ':j'=>$id]);
          $existing = (int)($stmt->fetchColumn() ?: 0);

          if ($existing) {
            $up = $pdo->prepare("UPDATE Avaliacao SET nota = :n, justificativa = :t, data_avaliacao = NOW() WHERE id_avaliacao = :id");
            $up->execute([':n'=>$nota, ':t'=>$just, ':id'=>$existing]);
            $flash = 'Avaliação atualizada.';
          } else {
            $ins = $pdo->prepare("INSERT INTO Avaliacao (id_usuario, id_jogo, justificativa, nota) VALUES (:u, :j, :t, :n)");
            $ins->execute([':u'=>$userId, ':j'=>$id, ':t'=>$just, ':n'=>$nota]);
            $flash = 'Avaliação publicada.';
          }
        }
      }
    }

    if ($action === 'delete_review') {
      if (!$userId) {
        $flash = 'Você precisa estar logado.';
      } else {
        $rid = (int)($_POST['rid'] ?? 0);
        if ($rid > 0) {
          $del = $pdo->prepare("DELETE FROM Avaliacao WHERE id_avaliacao = :id AND id_usuario = :u AND id_jogo = :j");
          $del->execute([':id'=>$rid, ':u'=>$userId, ':j'=>$id]);
          $flash = $del->rowCount() ? 'Avaliação excluída.' : 'Avaliação não encontrada.';
        }
      }
    }
  } catch (Throwable $e) {
    $flash = 'Erro: ' . $e->getMessage();
  }

  // PRG (post-redirect-get) pra evitar re-envio
  $_SESSION['flash'] = $flash;
  header('Location: aval-jogo.php?id=' . $id);
  exit;
}

// captura e exibe flash
if (isset($_SESSION['flash'])) {
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
}

// ----------------- lê dados do jogo -----------------
$stmt = $pdo->prepare("
  SELECT id_jogo, titulo, descricao, desenvolvedora, data_lancamento, link_compra, plataforma
  FROM Jogo
  WHERE id_jogo = :id
  LIMIT 1
");
$stmt->execute([':id'=>$id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
  http_response_code(404);
  echo "<!doctype html><meta charset='utf-8'><style>body{font-family:system-ui;background:#0b0b0c;color:#eee;padding:3rem}</style><h1>Jogo não encontrado</h1><p><a href='index.php' style='color:#ffd300'>Voltar</a></p>";
  exit;
}

// gêneros
$genresStmt = $pdo->prepare("
  SELECT g.nome_genero
  FROM Jogo_Genero jg
  JOIN Genero g ON g.id_genero = jg.id_genero
  WHERE jg.id_jogo = :id
  ORDER BY g.nome_genero
");
$genresStmt->execute([':id'=>$id]);
$genres = array_map(fn($r)=>$r['nome_genero'], $genresStmt->fetchAll(PDO::FETCH_ASSOC));

// imagens
$poster = $pdo->prepare("SELECT caminho FROM Jogo_Imagem WHERE id_jogo = :id AND tipo = 'poster' ORDER BY ordem_exib LIMIT 1");
$poster->execute([':id'=>$id]);
$posterPath = $poster->fetchColumn() ?: 'assets/img/poster.png';

$banner = $pdo->prepare("SELECT caminho FROM Jogo_Imagem WHERE id_jogo = :id AND tipo = 'banner' ORDER BY ordem_exib LIMIT 1");
$banner->execute([':id'=>$id]);
$bannerPath = $banner->fetchColumn() ?: $posterPath;

$shots = $pdo->prepare("SELECT id_imagem, caminho, ordem_exib FROM Jogo_Imagem WHERE id_jogo = :id AND tipo = 'screenshot' ORDER BY ordem_exib ASC, id_imagem ASC");
$shots->execute([':id'=>$id]);
$screens = $shots->fetchAll(PDO::FETCH_ASSOC);

// média/contagem
$avgStmt = $pdo->prepare("SELECT COUNT(*) AS c, AVG(nota) AS a FROM Avaliacao WHERE id_jogo = :id");
$avgStmt->execute([':id'=>$id]);
$stats = $avgStmt->fetch(PDO::FETCH_ASSOC);
$reviewsCount = (int)($stats['c'] ?? 0);
$avgScore     = $reviewsCount ? round((float)$stats['a'], 1) : 0.0;

// avaliações (com usuário)
$revStmt = $pdo->prepare("
  SELECT 
    a.id_avaliacao   AS id,
    a.nota           AS nota,
    a.justificativa  AS texto,
    a.data_avaliacao AS data,
    u.id_usuario     AS uid,
    u.nome_usuario   AS nome
  FROM Avaliacao a
  JOIN Usuario u ON u.id_usuario = a.id_usuario
  WHERE a.id_jogo = :id
  ORDER BY a.data_avaliacao DESC, a.id_avaliacao DESC
");
$revStmt->execute([':id'=>$id]);
$rawReviews = $revStmt->fetchAll(PDO::FETCH_ASSOC);

// prepara dados p/ JS
$MEDIA = array_map(fn($r)=>[
  'src' => $toWeb($r['caminho']),
  'alt' => 'Screenshot'
], $screens);

// avatar mockado (não há coluna de avatar na tabela)
$REVIEWS = array_map(function($r){
  $avatarSeed = 10 + ((int)$r['uid'] % 60);
  return [
    'id'     => (int)$r['id'],
    'user'   => (string)$r['nome'],
    'avatar' => "https://i.pravatar.cc/96?img={$avatarSeed}",
    'date'   => (new DateTime($r['data']))->format('Y-m-d'),
    'score'  => (float)$r['nota'],
    'text'   => (string)($r['texto'] ?? ''),
    'helpful'=> 0,
    'mine'   => isset($_SESSION['Usuario']['Id']) && (int)$_SESSION['Usuario']['Id'] === (int)$r['uid'],
  ];
}, $rawReviews);

// relacionados: outros jogos que compartilham algum gênero
$relStmt = $pdo->prepare("
  SELECT 
    j2.id_jogo,
    j2.titulo,
    COALESCE(p.caminho, 'assets/img/poster.png') AS poster,
    ROUND(COALESCE(AVG(a2.nota),0),1) AS media
  FROM Jogo j2
  JOIN Jogo_Genero jg2 ON jg2.id_jogo = j2.id_jogo
  LEFT JOIN Jogo_Imagem p ON p.id_jogo = j2.id_jogo AND p.tipo = 'poster'
  LEFT JOIN Avaliacao a2 ON a2.id_jogo = j2.id_jogo
  WHERE jg2.id_genero IN (SELECT id_genero FROM Jogo_Genero WHERE id_jogo = :id)
    AND j2.id_jogo <> :id
  GROUP BY j2.id_jogo, j2.titulo, p.caminho
  ORDER BY media DESC, j2.titulo ASC
  LIMIT 12
");
$relStmt->execute([':id'=>$id]);
$RELATED = array_map(fn($r)=>[
  'id'    => (int)$r['id_jogo'],
  'title' => (string)$r['titulo'],
  'cover' => $toWeb($r['poster']),
  'score' => (float)$r['media'],
], $relStmt->fetchAll(PDO::FETCH_ASSOC));

// dados básicos pra página
$title         = (string)$game['titulo'];
$desc          = (string)($game['descricao'] ?? '');
$dev           = (string)($game['desenvolvedora'] ?? '');
$releaseStr    = $game['data_lancamento'] ? (new DateTime($game['data_lancamento']))->format('d/m/Y') : '—';
$buyLink       = (string)($game['link_compra'] ?: '');
$platformLabel = (string)($game['plataforma'] ?: '');
$posterURL     = $toWeb($posterPath);
$bannerURL     = $toWeb($bannerPath);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Storm — <?= htmlspecialchars($title, ENT_QUOTES) ?></title>
  <meta name="color-scheme" content="dark light" />
  <link rel="stylesheet" href="./assets/css/styles-aval.css" />
  <link rel="icon" href="./assets/Favicon/logo-sem-fundo.png" />
</head>
<body>

<?php if ($flash): ?>
  <div class="toast" role="status" aria-live="polite" style="position:fixed;top:12px;left:50%;transform:translateX(-50%);z-index:9999;background:#222;color:#fff;padding:.6rem 1rem;border-radius:.5rem;box-shadow:0 6px 20px rgba(0,0,0,.35)">
    <?= htmlspecialchars($flash, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') ?>
  </div>
  <script>setTimeout(()=>document.querySelector('.toast')?.remove(), 4200);</script>
<?php endif; ?>

<div class="app" aria-live="polite">
  <!-- ============ SIDEBAR ============ -->
  <aside id="sidebar" class="sidebar compact" aria-label="Navegação principal">
    <div class="brand">
      <a class="brand__avatar" href="index.php" aria-label="Storm — Homepage">
        <img id="siteLogo" src="./assets/Favicon/logo-sem-fundo.png" alt="Logo Storm"
             onerror="this.replaceWith(this.nextElementSibling)" />
        <svg class="brand__avatar-fallback" viewBox="0 0 48 48" aria-hidden="true">
          <circle cx="24" cy="24" r="23" fill="none" stroke="currentColor" stroke-width="2"/>
          <path d="M18 30 30 8l-4 10h8L22 40l4-10z" fill="currentColor"/>
        </svg>
      </a>

      <a href="index.php" class="brand__title-wrap">
        <strong class="brand__title label">Storm.</strong>
      </a>

      <button id="toggleSidebar" class="btn btn--icon" title="Expandir/Recolher menu"
              aria-expanded="false" aria-controls="sidebar">
        <span class="sr-only">Alternar sidebar</span>⟷
      </button>
    </div>

    <nav class="nav">
      <div class="nav__group">
        <h6 class="nav__heading label">Menu</h6>

        <a class="nav__item" href="index.php">
          <span class="nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22"><path d="M12 3 3 11h2v8a2 2 0 0 0 2 2h4v-6h2v6h4a2 2 0 0 0 2-2v-8h2L12 3z"/></svg>
          </span>
          <span class="label">Homepage</span>
        </a>

        <a class="nav__item" href="<?= CAMINHO_VIEWS ?>FormSugestao.php">
          <span class="nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22"><path d="M12 2a7 7 0 0 1 4 12c-.7.6-1 1.1-1 2v1H9v-1c0-.9-.3-1.4-1-2A7 7 0 0 1 12 2zm-3 17h6v2H9v-2z"/></svg>
          </span>
          <span class="label">Sugestões de Jogos</span>
        </a>
      </div>

      <div class="nav__group">
        <h6 class="nav__heading label">Social</h6>

        <a class="nav__item" href="perfil.php">
          <span class="nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/></svg>
          </span>
          <span class="label">Perfil</span>
        </a>
      </div>
    </nav>

    <div class="sidebar__bottom">
      <label class="switch" title="Modo escuro/claro">
        <input id="themeToggle" type="checkbox" />
        <span class="switch__track" aria-hidden="true"><span class="switch__thumb"></span></span>
        <span class="label">Modo Escuro</span>
      </label>
    </div>
  </aside>

  <!-- ============ CONTEÚDO PRINCIPAL ============ -->
  <main id="main" class="game" tabindex="-1">
    <!-- HERO -->
    <section class="hero">
      <figure class="hero__backdrop">
        <img id="trailerThumb" src="<?= htmlspecialchars($bannerURL, ENT_QUOTES) ?>" alt="Banner de <?= htmlspecialchars($title, ENT_QUOTES) ?>" />
      </figure>

      <!-- Poster sobreposto -->
      <figure class="poster">
        <img id="posterImg" src="<?= htmlspecialchars($posterURL, ENT_QUOTES) ?>" alt="Capa de <?= htmlspecialchars($title, ENT_QUOTES) ?>" />
      </figure>

      <!-- Meta básica -->
      <div class="meta">
        <h1 class="title"><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>

        <ul class="facts">
          <li><strong>Desenvolvedora:</strong> <?= htmlspecialchars($dev ?: '—', ENT_QUOTES) ?></li>
          <li><strong>Lançamento:</strong> <?= htmlspecialchars($releaseStr, ENT_QUOTES) ?></li>
          <li class="platforms">
            <strong>Plataforma:</strong>
            <span class="pf" title="PC" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M3 5h18v9H3zM2 16h8v2H2zm10 0h10v2H12z"></path></svg>
            </span>
            <em class="muted">pc</em>
          </li>
          <li><strong>Gênero:</strong> <?= htmlspecialchars(implode(', ', $genres) ?: '—', ENT_QUOTES) ?></li>
        </ul>

        <p class="desc">
          <?= nl2br(htmlspecialchars($desc ?: 'Sem descrição.', ENT_QUOTES)) ?>
        </p>

        <div class="rating">
          <div class="rating__avg" title="Média dos usuários">⭐ <b id="avgScore"><?= number_format($avgScore, 1, '.', '') ?></b> <span class="muted">/ 10</span></div>
          <div class="rating__count"><span id="reviewCount"><?= number_format($reviewsCount, 0, '', '.') ?></span> reviews</div>
          <?php if ($buyLink): ?>
            <a class="btn btn--primary" id="buyNow" href="<?= htmlspecialchars($buyLink, ENT_QUOTES) ?>" target="_blank" rel="noopener">Comprar agora</a>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- GALERIA -->
    <section class="section gallery" aria-label="Galeria de mídia">
      <header class="section__header">
        <h2>Mídia</h2>
      </header>
      <div id="galleryGrid" class="gallery__grid" role="list"></div>
    </section>

    <!-- AVALIAÇÕES / COMENTÁRIOS -->
    <section class="section reviews" aria-label="Avaliações e Comentários">
      <header class="section__header reviews__header">
        <h2>Avaliações e Comentários</h2>
        <div class="filters">
          <label>Ordenar:
            <select id="reviewSort">
              <option value="recent">Mais recentes</option>
              <option value="high">Notas mais altas</option>
              <option value="low">Notas mais baixas</option>
            </select>
          </label>
        </div>
      </header>

      <!-- criar avaliação -->
      <form id="newReview" class="newreview" method="post" autocomplete="off">
        <input type="hidden" name="action" value="new_review">
        <input type="hidden" name="nota" id="notaInput" value="">
        <div class="stars" id="starInput" aria-label="Sua nota" role="radiogroup"></div>
        <textarea id="reviewText" name="just" rows="3" placeholder="Escreva sua avaliação..."></textarea>
        <div class="newreview__actions">
          <button type="reset" class="btn btn--ghost">Limpar</button>
          <button type="submit" class="btn btn--primary" <?= empty($_SESSION['Usuario']['Id']) ? 'disabled title="Entre para avaliar"' : '' ?>>Publicar</button>
        </div>
        <?php if (empty($_SESSION['Usuario']['Id'])): ?>
          <small class="muted">Você precisa <a href="<?= CAMINHO_VIEWS ?>loginUsuario.php">entrar</a> para avaliar.</small>
        <?php endif; ?>
      </form>

      <div id="reviewsList" class="reviews__list" role="list"></div>
    </section>

    <!-- RELACIONADOS -->
    <section class="section related" aria-label="Quem gostou de <?= htmlspecialchars($title, ENT_QUOTES) ?> também gostou de">
      <header class="section__header">
        <h2>Relacionados</h2>
        <div class="section__controls">
          <button class="btn btn--icon" id="relPrev" aria-label="Anterior">‹</button>
          <button class="btn btn--icon" id="relNext" aria-label="Próximo">›</button>
        </div>
      </header>
      <div id="relatedRail" class="rail" role="list" tabindex="0"></div>
    </section>
  </main>
</div>

<!-- ============ LIGHTBOX ============ -->
<div id="lightbox" class="lightbox" hidden aria-hidden="true" aria-label="Visualizador de mídia" role="dialog">
  <button class="lightbox__close" id="lbClose" aria-label="Fechar (Esc)">✕</button>
  <button class="lightbox__nav prev" id="lbPrev" aria-label="Anterior">‹</button>
  <figure class="lightbox__figure">
    <img id="lbImage" alt="Mídia em destaque" />
    <figcaption id="lbCaption" class="sr-only"></figcaption>
  </figure>
  <button class="lightbox__nav next" id="lbNext" aria-label="Próximo">›</button>
</div>

<!-- ======= TEMPLATES ======= -->
<template id="tpl-review">
  <article class="review" role="listitem" tabindex="0">
    <header class="review__header">
      <div class="review__user">
        <img class="review__avatar" alt="" />
        <div class="review__id">
          <strong class="review__name"></strong>
          <time class="review__date" datetime=""></time>
        </div>
      </div>
      <div class="review__score" title="Nota do usuário"></div>
    </header>
    <p class="review__text"></p>
    <div class="review__actions">
      <form class="deleteForm" method="post" style="display:none">
        <input type="hidden" name="action" value="delete_review">
        <input type="hidden" name="rid" value="">
      </form>
      <button class="btn btn--tiny btn--danger delete">Excluir</button>
    </div>
  </article>
</template>

<template id="tpl-related-card">
  <article class="card" role="listitem" tabindex="0">
    <div class="card__media">
      <img class="card__img" alt="" loading="lazy" />
    </div>
    <div class="card__body">
      <h3 class="card__title"></h3>
      <span class="card__score"></span>
    </div>
  </article>
</template>

<!-- ======= DADOS PARA JS ======= -->
<script>
  window.MEDIA   = <?= json_encode($MEDIA,   JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  window.REVIEWS = <?= json_encode($REVIEWS, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  window.RELATED = <?= json_encode($RELATED, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>

<!-- ======= JS ======= -->
<script>
/* Helpers */
const $  = (s, el=document)=>el.querySelector(s);
const $$ = (s, el=document)=>[...el.querySelectorAll(s)];
const storage = {
  get(k,f=null){ try{const v=localStorage.getItem(k); return v?JSON.parse(v):f;}catch{return f;} },
  set(k,v){ try{localStorage.setItem(k,JSON.stringify(v));}catch{} }
};
const fmtDate = (iso)=> new Date(iso).toLocaleDateString('pt-BR', {day:'2-digit',month:'short',year:'numeric'});

/* Tema */
(function ThemeManager(){
  const root = document.documentElement;
  const toggle = $('#themeToggle');
  const key = 'storm:theme';
  function apply(theme){
    root.setAttribute('data-theme', theme);
    if (toggle) toggle.checked = theme === 'dark';
    storage.set(key, theme);
  }
  const saved = storage.get(key) || 'dark';
  apply(saved);
  toggle?.addEventListener('change', ()=>apply(toggle.checked ? 'dark' : 'light'));
})();

/* Sidebar */
(function Sidebar(){
  const el  = $('#sidebar');
  const btn = $('#toggleSidebar');
  const key = 'storm:sidebar';
  const preferExpanded = () => window.innerWidth > 760;
  function set(expanded){
    el.classList.toggle('expanded', expanded);
    el.classList.toggle('compact', !expanded);
    btn?.setAttribute('aria-expanded', String(expanded));
    storage.set(key, expanded ? 'expanded' : 'compact');
  }
  const saved = storage.get(key);
  set(saved ? saved === 'expanded' : preferExpanded());
  btn?.addEventListener('click', ()=>set(!el.classList.contains('expanded')));
  addEventListener('resize', ()=>{
    const shouldExpand = preferExpanded();
    if (shouldExpand && el.classList.contains('compact')) set(true);
    if (!shouldExpand && el.classList.contains('expanded')) set(false);
  });
})();

/* ---------- Galeria + Lightbox ---------- */
(function Gallery(){
  const grid = $('#galleryGrid');
  (window.MEDIA||[]).forEach((m, i)=>{
    const btn = document.createElement('button');
    btn.className = 'gallery__item';
    btn.innerHTML = `<img src="${m.src}" alt="${m.alt}">`;
    btn.addEventListener('click', ()=>Lightbox.open(i));
    grid.appendChild(btn);
  });
})();

const Lightbox = (()=>{
  const root = $('#lightbox');
  const img  = $('#lbImage');
  const cap  = $('#lbCaption');
  let index = 0;
  const DATA = window.MEDIA||[];

  function show(i){
    if(!DATA.length) return;
    index = (i + DATA.length) % DATA.length;
    const m = DATA[index];
    img.src = m.src;
    img.alt = m.alt || '';
    cap.textContent = m.alt || '';
  }

  function open(i=0){
    if(!DATA.length) return;
    show(i);
    root.hidden = false;
    root.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
    $('#lbClose').focus();
  }
  function close(){
    root.hidden = true;
    root.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  }
  function next(){ show(index+1); }
  function prev(){ show(index-1); }

  $('#lbClose')?.addEventListener('click', close);
  $('#lbNext')?.addEventListener('click', next);
  $('#lbPrev')?.addEventListener('click', prev);
  root?.addEventListener('click', (e)=>{ if(e.target===root) close(); });
  window.addEventListener('keydown', (e)=>{
    if(root.hidden) return;
    if(e.key==='Escape') close();
    if(e.key==='ArrowRight') next();
    if(e.key==='ArrowLeft') prev();
  });

  return { open, close, next, prev };
})();

/* ---------- Stars + submit nota ---------- */
(function StarInput(){
  const wrap = $('#starInput');
  for(let i=1;i<=10;i++){
    const b = document.createElement('button');
    b.type='button';
    b.className='star';
    b.dataset.value = i;
    b.innerHTML = `<svg viewBox="0 0 24 24"><path d="m12 2 2.9 6.1 6.7.9-4.8 4.6 1.2 6.7L12 17.9 6 20.3l1.2-6.7L2.4 9l6.7-.9L12 2z"/></svg>`;
    wrap.appendChild(b);
  }
  let current = 0;
  const update = (n)=>{
    current = n;
    $$('.star', wrap).forEach((el,idx)=> el.classList.toggle('is-on', idx < n));
    $('#notaInput').value = String(current);
  };
  wrap.addEventListener('mouseover', e=>{
    const v = +e.target.closest('.star')?.dataset.value || 0; if(v) update(v);
  });
  wrap.addEventListener('mouseleave', ()=>update(current));
  wrap.addEventListener('click', e=>{
    const v = +e.target.closest('.star')?.dataset.value || 0; if(v) update(v);
  });
  update(0);
})();

/* ---------- Reviews ---------- */
function renderReview(r){
  const tpl = $('#tpl-review').content.cloneNode(true);
  const root = tpl.querySelector('.review');
  tpl.querySelector('.review__avatar').src = r.avatar;
  tpl.querySelector('.review__avatar').alt = `Avatar de ${r.user}`;
  tpl.querySelector('.review__name').textContent = r.user;
  const time = tpl.querySelector('.review__date');
  time.textContent = fmtDate(r.date);
  time.dateTime = r.date;
  tpl.querySelector('.review__score').textContent = `⭐ ${Number(r.score).toFixed(1)}/10`;
  tpl.querySelector('.review__text').textContent = r.text;

  const delBtn = tpl.querySelector('.delete');
  const delForm = tpl.querySelector('.deleteForm');
  delForm.rid.value = r.id;

  if (!r.mine) {
    delBtn.style.display = 'none';
  } else {
    delBtn.addEventListener('click', ()=>{
      if (confirm('Excluir sua avaliação?')) {
        delForm.submit();
      }
    });
  }
  return tpl;
}

function sortReviews(mode){
  const data = [...(window.REVIEWS||[])];
  if(mode==='recent')  data.sort((a,b)=> new Date(b.date)-new Date(a.date));
  if(mode==='high')    data.sort((a,b)=> b.score-a.score);
  if(mode==='low')     data.sort((a,b)=> a.score-b.score);
  return data;
}

function loadReviews(){
  const list = $('#reviewsList');
  const mode = $('#reviewSort').value;
  const items = sortReviews(mode);
  list.innerHTML = items.length ? '' : '<p class="muted" style="padding:1rem">Ainda não há avaliações.</p>';
  items.forEach(r=> list.appendChild(renderReview(r)));
}
$('#reviewSort')?.addEventListener('change', loadReviews);
loadReviews();

/* ---------- Relacionados ---------- */
function renderRelatedCard(g){
  const tpl = $('#tpl-related-card').content.cloneNode(true);
  tpl.querySelector('.card__img').src = g.cover;
  tpl.querySelector('.card__img').alt = `Capa de ${g.title}`;
  tpl.querySelector('.card__title').textContent = g.title;
  tpl.querySelector('.card__score').textContent = g.score ? `⭐ ${g.score.toFixed(1)}` : '—';
  tpl.querySelector('.card').addEventListener('click', ()=> {
    window.location.href = `aval-jogo.php?id=${encodeURIComponent(g.id)}`;
  });
  return tpl;
}

(function RelatedRail(){
  const rail = $('#relatedRail');
  (window.RELATED||[]).forEach(g=> rail.appendChild(renderRelatedCard(g)));

  const prev = $('#relPrev');
  const next = $('#relNext');
  const scroll = (x)=> rail.scrollBy({left:x, behavior:'smooth'});
  prev.addEventListener('click', ()=>scroll(-400));
  next.addEventListener('click', ()=>scroll( 400));
  rail.addEventListener('wheel', (e)=>{
    if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
      e.preventDefault();
      scroll(e.deltaY * .7);
    }
  }, {passive:false});
})();
</script>

</body>
</html>
