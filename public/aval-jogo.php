<?php
// public/aval-jogo.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\DB\Conexao;

// URLs de views para links/redirects
const CAMINHO_VIEWS = './../App/Views/';

// util simples de escape
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// ---------- Obtém ID do jogo ----------
$idJogo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idJogo || $idJogo <= 0) {
  header('Location: index.php');
  exit;
}

// ---------- Conexão ----------
$pdo = Conexao::getInstancia();
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

// ---------- Lê o jogo + gêneros ----------
$sqlJogo = "
  SELECT 
    j.id_jogo,
    j.titulo,
    j.descricao,
    j.desenvolvedora,
    j.data_lancamento,
    j.link_compra,
    j.plataforma,
    GROUP_CONCAT(g.nome_genero ORDER BY g.nome_genero SEPARATOR ', ') AS generos
  FROM Jogo j
  LEFT JOIN Jogo_Genero jg ON jg.id_jogo = j.id_jogo
  LEFT JOIN Genero g       ON g.id_genero = jg.id_genero
  WHERE j.id_jogo = :id
  GROUP BY j.id_jogo
";
$stmt = $pdo->prepare($sqlJogo);
$stmt->execute([':id' => $idJogo]);
$game = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$game) {
  header('Location: index.php');
  exit;
}

// ---------- Imagens (poster, banner, screenshots) ----------
$poster = $pdo->prepare("SELECT caminho FROM Jogo_Imagem WHERE id_jogo = :id AND tipo = 'poster' ORDER BY ordem_exib LIMIT 1");
$poster->execute([':id' => $idJogo]);
$posterPath = $poster->fetchColumn() ?: '/assets/img/poster.png';

$banner = $pdo->prepare("SELECT caminho FROM Jogo_Imagem WHERE id_jogo = :id AND tipo = 'banner' ORDER BY ordem_exib LIMIT 1");
$banner->execute([':id' => $idJogo]);
$bannerPath = $banner->fetchColumn() ?: '/assets/img/banner.png';

$shots = $pdo->prepare("SELECT caminho FROM Jogo_Imagem WHERE id_jogo = :id AND tipo = 'screenshot' ORDER BY ordem_exib");
$shots->execute([':id' => $idJogo]);
$screens = $shots->fetchAll(\PDO::FETCH_COLUMN);

// ---------- Média e contagem de avaliações ----------
$stats = $pdo->prepare("SELECT ROUND(AVG(nota),1) AS media, COUNT(*) AS total FROM Avaliacao WHERE id_jogo = :id");
$stats->execute([':id' => $idJogo]);
$mediaAv = (float)($stats->fetch(\PDO::FETCH_ASSOC)['media'] ?? 0);
$countAv = (int)($stats->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0); // cuidado: fetch já foi feito
// corrigindo a leitura dupla: refaz a consulta de forma segura
$stats->execute([':id' => $idJogo]);
$rowStats = $stats->fetch(\PDO::FETCH_ASSOC) ?: ['media'=>null,'total'=>0];
$mediaAv = $rowStats['media'] !== null ? (float)$rowStats['media'] : 0.0;
$countAv = (int)$rowStats['total'];

// ---------- Lista de avaliações (com usuário) ----------
$sqlReviews = "
  SELECT 
    a.id_avaliacao   AS id,
    a.nota           AS score,
    a.justificativa  AS text,
    a.data_avaliacao AS dt,
    u.nome_usuario   AS user,
    u.id_usuario     AS uid
  FROM Avaliacao a
  INNER JOIN Usuario u ON u.id_usuario = a.id_usuario
  WHERE a.id_jogo = :id
  ORDER BY a.data_avaliacao DESC, a.id_avaliacao DESC
";
$stmtR = $pdo->prepare($sqlReviews);
$stmtR->execute([':id' => $idJogo]);
$reviewRows = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

// ---------- “Relacionados”: outros jogos (ordenados pela média, depois id) ----------
$sqlRelated = "
  SELECT 
    j.id_jogo,
    j.titulo,
    COALESCE(p.caminho, '/assets/img/poster.png') AS poster,
    m.media
  FROM Jogo j
  LEFT JOIN (
    SELECT id_jogo, ROUND(AVG(nota),1) AS media
    FROM Avaliacao
    GROUP BY id_jogo
  ) m ON m.id_jogo = j.id_jogo
  LEFT JOIN (
    SELECT id_jogo, caminho
    FROM Jogo_Imagem
    WHERE tipo = 'poster'
    GROUP BY id_jogo
  ) p ON p.id_jogo = j.id_jogo
  WHERE j.id_jogo <> :id
  ORDER BY (m.media IS NULL), m.media DESC, j.id_jogo DESC
  LIMIT 12
";
$stmtRel = $pdo->prepare($sqlRelated);
$stmtRel->execute([':id' => $idJogo]);
$relatedRows = $stmtRel->fetchAll(\PDO::FETCH_ASSOC);

// ---------- Helpers de caminho web ----------
$basePath   = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$basePrefix = $basePath === '' ? '' : $basePath;
$toWeb = function (?string $p) use ($basePrefix): string {
  $p = (string)($p ?? '');
  if ($p === '') return '';
  if (preg_match('#^https?://#i', $p)) return $p;
  $p = ltrim($p, '/');
  return ($basePrefix ? $basePrefix.'/' : '/') . $p;
};

// Dados para o front
$MEDIA = array_map(fn($c) => ['src' => $toWeb($c), 'alt' => 'Screenshot'], $screens ?: []);
$REVIEWS = array_map(function(array $r) {
  // avatar dummy estável por usuário
  $avatar = 'https://i.pravatar.cc/96?u=' . urlencode((string)$r['uid']);
  $iso = (new DateTime($r['dt']))->format('Y-m-d');
  return [
    'id'     => (int)$r['id'],
    'user'   => (string)$r['user'],
    'avatar' => $avatar,
    'date'   => $iso,
    'score'  => (float)$r['score'],
    'text'   => (string)($r['text'] ?? ''),
  ];
}, $reviewRows);

$RELATED = array_map(function(array $r) use ($toWeb) {
  return [
    'id'    => (int)$r['id_jogo'],
    'title' => (string)$r['titulo'],
    'cover' => $toWeb((string)$r['poster']),
    'score' => isset($r['media']) ? (float)$r['media'] : null
  ];
}, $relatedRows);

// Campos do jogo para exibir
$titulo         = (string)$game['titulo'];
$descricao      = (string)($game['descricao'] ?? '');
$desenvolvedora = (string)($game['desenvolvedora'] ?? '');
$dataLanc       = $game['data_lancamento'] ? (new DateTime($game['data_lancamento']))->format('d/m/Y') : '—';
$linkCompra     = (string)($game['link_compra'] ?? '');
$plataforma     = strtolower(trim((string)($game['plataforma'] ?? '')));
$generos        = (string)($game['generos'] ?? '');

$posterWeb = $toWeb($posterPath);
$bannerWeb = $toWeb($bannerPath);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Storm — <?= e($titulo) ?></title>
  <meta name="color-scheme" content="dark light" />
  <link rel="stylesheet" href="./assets/css/styles-aval.css" />
  <link rel="icon" href="./assets/img/logo-sem-fundo.png" />
  <style>
    /* Centralização do bloco Plataforma (label + ícone + nome) */
    .facts .platforms{
      display:flex; align-items:center; justify-content:center; gap:.5rem; text-align:center; flex-wrap:wrap;
    }
    .facts .platforms .pf{ width:26px; height:26px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; }
    .facts .platforms .pf svg{ width:20px; height:20px; display:block; }
    .facts .platforms em{ font-style:normal; opacity:.8; }
  </style>
</head>
<body>
<div class="app" aria-live="polite">
  <!-- ============ SIDEBAR ============ -->
  <aside id="sidebar" class="sidebar compact" aria-label="Navegação principal">
    <div class="brand">
      <a class="brand__avatar" href="index.php" aria-label="Storm — Homepage">
        <img id="siteLogo" src="./assets/img/logo-sem-fundo.png" alt="Logo Storm"
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
        <img id="trailerThumb" src="<?= e($bannerWeb) ?>" alt="Banner de <?= e($titulo) ?>" />
      </figure>

      <!-- Poster sobreposto -->
      <figure class="poster">
        <img id="posterImg" src="<?= e($posterWeb) ?>" alt="Capa de <?= e($titulo) ?>" />
      </figure>

      <!-- Meta básica -->
      <div class="meta">
        <h1 class="title"><?= e($titulo) ?></h1>

        <ul class="facts">
          <li><strong>Desenvolvedora:</strong> <?= e($desenvolvedora ?: '—') ?></li>
          <li><strong>Lançamento:</strong> <?= e($dataLanc) ?></li>
          <li class="platforms">
            <strong>Plataforma:</strong>
            <span class="pf" title="<?= e($plataforma ?: '—') ?>" aria-hidden="true">
              <!-- Ícone genérico de PC/monitor; ajuste se quiser mapear por plataforma -->
              <svg viewBox="0 0 24 24"><path d="M3 5h18v9H3zM2 16h8v2H2zm10 0h10v2H12z"></path></svg>
            </span>
            <em class="muted"><?= e($plataforma ?: '—') ?></em>
          </li>
          <li><strong>Gênero:</strong> <?= e($generos ?: '—') ?></li>
        </ul>

        <p class="desc">
          <?= nl2br(e($descricao ?: 'Sem descrição.')) ?>
        </p>

        <div class="rating">
          <div class="rating__avg" title="Média dos usuários">⭐ <b id="avgScore"><?= number_format($mediaAv, 1, ',', '') ?></b> <span class="muted">/ 10</span></div>
          <div class="rating__count"><span id="reviewCount"><?= number_format($countAv, 0, ',', '.') ?></span> reviews</div>
          <?php if ($linkCompra): ?>
            <a class="btn btn--ghost" id="buyNow" href="<?= e($linkCompra) ?>" target="_blank" rel="noopener">Comprar agora</a>
          <?php endif; ?>
          <a class="btn btn--primary" id="btnDoReview" href="<?= CAMINHO_VIEWS . 'CadastrarAvaliacao.php?id_jogo=' . (int)$idJogo ?>">Faça sua avaliação</a>
        </div>
      </div>
    </section>

    <!-- GALERIA -->
    <section class="section gallery" aria-label="Galeria de mídia">
      <header class="section__header">
        <h2>Mídia</h2>
      </header>
      <div id="galleryGrid" class="gallery__grid" role="list">
        <!-- JS insere thumbs -->
      </div>
    </section>

    <!-- AVALIAÇÕES (somente listagem) -->
    <section class="section reviews" aria-label="Avaliações e Comentários">
      <header class="section__header reviews__header">
        <h2>Avaliações</h2>
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

      <div id="reviewsList" class="reviews__list" role="list">
        <!-- JS injeta cards -->
      </div>
    </section>

    <!-- RELACIONADOS -->
    <section class="section related" aria-label="Relacionados">
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

<!-- ============ LIGHTBOX (modal de mídia) ============ -->
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

<!-- ======= DADOS DO BACK-END ======= -->
<script>
  window.STORM = {
    media: <?= json_encode($MEDIA,   JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>,
    reviews: <?= json_encode($REVIEWS, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>,
    related: <?= json_encode($RELATED, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>,
    avg: <?= json_encode($mediaAv) ?>,
    count: <?= json_encode($countAv) ?>,
    title: <?= json_encode($titulo, JSON_UNESCAPED_UNICODE) ?>
  };
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

/* ---------------------- GALERIA + LIGHTBOX ---------------------- */
const MEDIA = Array.isArray(window.STORM?.media) ? window.STORM.media : [];

(function Gallery(){
  const grid = $('#galleryGrid');
  if (!MEDIA.length) {
    grid.innerHTML = '<p class="muted">Sem mídia cadastrada.</p>';
    return;
  }
  MEDIA.forEach((m, i)=>{
    const btn = document.createElement('button');
    btn.className = 'gallery__item';
    btn.innerHTML = `<img src="${m.src}" alt="${m.alt||'Mídia'}">`;
    btn.addEventListener('click', ()=>Lightbox.open(i));
    grid.appendChild(btn);
  });
})();

const Lightbox = (()=>{
  const root = $('#lightbox');
  const img  = $('#lbImage');
  const cap  = $('#lbCaption');
  let index = 0;

  function show(i){
    index = (i + MEDIA.length) % MEDIA.length;
    const m = MEDIA[index];
    img.src = m.src;
    img.alt = m.alt || 'Mídia';
    cap.textContent = m.alt || '';
  }

  function open(i=0){
    if (!MEDIA.length) return;
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

/* ---------------------- AVALIAÇÕES (listagem) ---------------------- */
const REVIEWS = Array.isArray(window.STORM?.reviews) ? window.STORM.reviews : [];

function renderReview(r){
  const tpl = $('#tpl-review').content.cloneNode(true);
  tpl.querySelector('.review__avatar').src = r.avatar;
  tpl.querySelector('.review__avatar').alt = `Avatar de ${r.user}`;
  tpl.querySelector('.review__name').textContent = r.user;
  const time = tpl.querySelector('.review__date');
  time.textContent = fmtDate(r.date);
  time.dateTime = r.date;
  tpl.querySelector('.review__score').textContent = `⭐ ${Number(r.score).toFixed(1)}/10`;
  tpl.querySelector('.review__text').textContent = r.text || '';
  return tpl;
}

function sortReviews(mode){
  const data = [...REVIEWS];
  if(mode==='recent')  data.sort((a,b)=> new Date(b.date)-new Date(a.date));
  if(mode==='high')    data.sort((a,b)=> b.score-a.score);
  if(mode==='low')     data.sort((a,b)=> a.score-b.score);
  return data;
}

function loadReviews(){
  const list = $('#reviewsList');
  const mode = $('#reviewSort').value;
  const items = sortReviews(mode);
  list.innerHTML = '';
  if (!items.length) {
    list.innerHTML = '<p class="muted">Ainda não há avaliações para este jogo.</p>';
    return;
  }
  items.forEach(r=> list.appendChild(renderReview(r)));
}
$('#reviewSort')?.addEventListener('change', loadReviews);
loadReviews();

// Atualiza média/contagem (garantia caso vindo do back seja 0)
(function SetupRating(){
  const avg = Number(window.STORM?.avg || 0);
  const cnt = Number(window.STORM?.count || 0);
  $('#avgScore').textContent = avg.toFixed(1).replace('.', ',');
  $('#reviewCount').textContent = cnt.toLocaleString('pt-BR');
})();

/* ---------------------- RELACIONADOS (carrossel) ---------------------- */
const RELATED = Array.isArray(window.STORM?.related) ? window.STORM.related : [];

function renderRelatedCard(g){
  const tpl = $('#tpl-related-card').content.cloneNode(true);
  tpl.querySelector('.card__img').src = g.cover;
  tpl.querySelector('.card__img').alt = `Capa de ${g.title}`;
  tpl.querySelector('.card__title').textContent = g.title;
  tpl.querySelector('.card__score').textContent = g.score!=null ? `⭐ ${Number(g.score).toFixed(1)}` : '';
  tpl.querySelector('.card').addEventListener('click', ()=> {
    window.open(`aval-jogo.php?id=${encodeURIComponent(g.id)}`, '_self');
  });
  return tpl;
}

(function RelatedRail(){
  const rail = $('#relatedRail');
  if (!RELATED.length) {
    rail.innerHTML = '<p class="muted" style="padding:1rem">Sem relacionados no momento.</p>';
    return;
  }
  RELATED.forEach(g=> rail.appendChild(renderRelatedCard(g)));

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
