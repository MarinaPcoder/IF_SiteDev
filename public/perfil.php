<?php
// public/perfil.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\DB\Conexao;

// URLs de views para links/redirects
const CAMINHO_VIEWS = './../App/Views/';

// ---------- Guard: precisa estar logado ----------
if (empty($_SESSION['Usuario']['Id'])) {
  $_SESSION['Mensagem_redirecionamento'] = "Você precisa estar logado para acessar o perfil.";
  header('Location: ' . CAMINHO_VIEWS . 'loginUsuario.php');
  exit;
}

$pdo = Conexao::getInstancia();
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$userId = (int) $_SESSION['Usuario']['Id'];

// ---------- Ações POST (logout e ir para deletar conta) ----------
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'logout') {
      unset($_SESSION['Usuario']);
      header('Location: ' . CAMINHO_VIEWS . 'loginUsuario.php');
      exit;
    }

    // Encaminha para tela de deletar usuário
    if ($action === 'delete') {
      header('Location: ' . CAMINHO_VIEWS . 'deletarUsuario.php');
      exit;
    }
  } catch (\Throwable $e) {
    $flash = 'Erro: ' . $e->getMessage();
  }
}

// ---------- Lê dados do usuário ----------
$stmtU = $pdo->prepare("SELECT id_usuario, nome_usuario, email, criado_em, bio FROM Usuario WHERE id_usuario = :id LIMIT 1");
$stmtU->execute([':id'=>$userId]);
$user = $stmtU->fetch(\PDO::FETCH_ASSOC) ?: ['nome_usuario'=>'', 'email'=>'', 'criado_em'=>date('Y-m-d H:i:s'), 'bio'=>''];

// ---------- Lê avaliações do usuário (com poster do jogo) ----------
$sqlPosterSub = "
  SELECT ji1.id_jogo, ji1.caminho
  FROM Jogo_Imagem ji1
  WHERE ji1.tipo = 'poster'
";
$sqlReviews = "
  SELECT 
    a.id_avaliacao AS id,
    a.nota,
    a.justificativa,
    a.data_avaliacao,
    j.id_jogo,
    j.titulo,
    COALESCE(p.caminho, 'assets/img/poster.png') AS poster
  FROM Avaliacao a
  INNER JOIN Jogo j ON j.id_jogo = a.id_jogo
  LEFT JOIN ($sqlPosterSub) p ON p.id_jogo = j.id_jogo
  WHERE a.id_usuario = :u
  ORDER BY a.data_avaliacao DESC, a.id_avaliacao DESC
";
$stmtR = $pdo->prepare($sqlReviews);
$stmtR->execute([':u'=>$userId]);
$rows = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

// normaliza caminhos respeitando subpasta
$basePath   = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$basePrefix = $basePath === '' ? '' : $basePath;
$toWeb = function (?string $p) use ($basePrefix): string {
  $p = (string)($p ?? '');
  if ($p === '') return '';
  if (preg_match('#^https?://#i', $p)) return $p;
  $p = ltrim($p, '/');
  return ($basePrefix ? $basePrefix.'/' : '/') . $p;
};

$reviews = [];
foreach ($rows as $r) {
  $reviews[] = [
    'id'     => (int)$r['id'],
    'game'   => (string)$r['titulo'],
    'gameId' => (int)$r['id_jogo'],
    'cover'  => $toWeb($r['poster'] ?? 'assets/img/poster.png'),
    'rating' => (float)$r['nota'],
    'date'   => (new \DateTime($r['data_avaliacao']))->format('Y-m-d'),
    'comment'=> (string)($r['justificativa'] ?? '')
  ];
}

$reviewsCount = count($reviews);
$username     = explode('@', (string)($user['email'] ?? ''))[0] ?? '';
$memberSince  = (new \DateTime($user['criado_em'] ?? 'now'))->format('d/m/Y');
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Storm — Perfil do Usuário</title>
  <meta name="color-scheme" content="dark light" />
  <link rel="stylesheet" href="./assets/css/perfil.css" />
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

        <a class="nav__item active" href="perfil.php">
          <span class="nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c-2.8 0-5-3.6-5-8z"/></svg>
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
  <main id="main" class="profile" tabindex="-1">
    <header class="profile__header">
      <h1 class="profile__title">Minhas Avaliações</h1>
      <div class="profile__actions"></div>
    </header>

    <section class="reviews" aria-label="Lista de avaliações do usuário">
      <div id="reviewList" class="reviews__list" role="list" tabindex="0">
        <!-- JS injeta <article class="review-card"> -->
      </div>

      <nav class="pagination" aria-label="Paginação" hidden>
        <button class="btn btn--ghost" id="pgPrev">Anterior</button>
        <span class="pagination__info" id="pgInfo">1 / 1</span>
        <button class="btn btn--ghost" id="pgNext">Próxima</button>
      </nav>
    </section>
  </main>

  <!-- ============ PAINEL À DIREITA (perfil resumido) ============ -->
  <aside class="aside" aria-label="Painel do usuário">
    <section class="usercard" aria-labelledby="uc-name">
      <div class="usercard__cover" aria-hidden="true"></div>

      <div class="usercard__header">
        <div class="usercard__id">
          <h2 id="uc-name" class="usercard__name"><?= htmlspecialchars($user['nome_usuario'] ?? '', ENT_QUOTES) ?></h2>
          <div class="usercard__user">@<?= htmlspecialchars($username, ENT_QUOTES) ?></div>
        </div>
      </div>

      <p class="usercard__bio"><?= htmlspecialchars($user['bio'] ?: 'Sem bio ainda.', ENT_QUOTES) ?></p>

      <ul class="usercard__meta">
        <li>
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M7 4h10v2H7V4zm-3 4h16v2H4V8zm3 4h10v2H7v-2zm-3 4h16v2H4v-2z"/></svg>
          </span>
          <span id="uc-since">Membro desde <?= htmlspecialchars(strtolower($memberSince), ENT_QUOTES) ?></span>
        </li>
        <li>
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 4h18v2H3V4zm2 4h14l-1 12H6L5 8zm5 2v8h2v-8H10zm-3 0 1 8h2l-1-8H7zm8 0-1 8h2l1-8h-2z"/></svg>
          </span>
          <span id="uc-count"><?= (int)$reviewsCount ?> avaliação<?= $reviewsCount===1?'':'es' ?></span>
        </li>
      </ul>

      <div class="usercard__actions">
        <a class="btn btn--primary" id="btnEditProfile" href="<?= CAMINHO_VIEWS ?>alterarUsuario.php">Editar Perfil</a>
        <form id="logoutForm" method="post" style="display:inline">
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="btn btn--ghost">Sair da conta atual</button>
        </form>
        <form id="DeleteForm" method="post" style="display:inline">
          <input type="hidden" name="action" value="delete">
          <button type="submit" class="btn btn--danger" id="btnDelete">Excluir conta</button>
        </form>
      </div>
    </section>
  </aside>
</div>

<!-- ======= TEMPLATES ======= -->
<template id="tpl-review-card">
  <article class="review-card" role="listitem" tabindex="0" data-id="">
    <div class="review-card__media">
      <img class="review-card__img" alt="" loading="lazy" />
    </div>

    <div class="review-card__body">
      <header class="review-card__header">
        <a class="review-card__title" target="_blank" rel="noopener"></a>
        <div class="review-card__meta">
          <span class="review-card__score" title="Nota Storm"></span>
          <time class="review-card__date" datetime=""></time>
        </div>
      </header>
      <p class="review-card__text"></p>

      <div class="review-card__actions">
        <button class="btn btn--tiny btn--ghost edit">
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 17.2V21h3.8l11-11.1-3.8-3.8L3 17.2zM20.7 7.0a1 1 0 0 0 0-1.4l-2.3-2.3a1 1 0 0 0-1.4 0l-1.7 1.7 3.8 3.8 1.6-1.8z"/></svg>
          </span>
          Editar
        </button>
        <button class="btn btn--tiny btn--danger delete">
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M6 7h12l-1 13H7L6 7zm12-3h-4l-1-1H11l-1 1H6v2h12V4z"/></svg>
          </span>
          Excluir
        </button>
      </div>
    </div>
  </article>
</template>

<!-- ======= DADOS DO BACK-END ======= -->
<script>
  window.STORM_REVIEWS = <?= json_encode($reviews, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  window.CAMINHO_VIEWS = <?= json_encode(CAMINHO_VIEWS, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
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

/* Store baseado no back-end */
const ReviewsStore = (()=> {
  const items = Array.isArray(window.STORM_REVIEWS) ? window.STORM_REVIEWS : [];
  return {
    all(){ return [...items]; },
    get(id){ return items.find(x=>x.id===id); }
  };
})();

/* Render de card de avaliação */
function renderReviewCard(model){
  const tpl = $('#tpl-review-card').content.cloneNode(true);
  const root = tpl.querySelector('.review-card');
  root.dataset.id = model.id;

  const img   = tpl.querySelector('.review-card__img');
  const title = tpl.querySelector('.review-card__title');
  const score = tpl.querySelector('.review-card__score');
  const date  = tpl.querySelector('.review-card__date');
  const text  = tpl.querySelector('.review-card__text');

  img.src = model.cover; img.alt = `Capa de ${model.game}`;
  title.textContent = model.game;
  title.href = `aval-jogo.php?id=${encodeURIComponent(model.gameId)}`;

  score.textContent = `⭐ ${Number(model.rating).toFixed(1)}/10`;
  date.textContent  = fmtDate(model.date);
  date.dateTime     = model.date;
  text.textContent  = model.comment || '';

  // EDITAR -> vai para AlterarAvaliacao.php?id=...
  tpl.querySelector('.edit').addEventListener('click', (e)=>{
    e.stopPropagation();
    const base = (window.CAMINHO_VIEWS || './');
    window.location.href = base + 'AlterarAvaliacao.php?id=' + encodeURIComponent(model.id);
  });

  // EXCLUIR -> confirma e vai para DeletarAvaliacao.php?id=...
  tpl.querySelector('.delete').addEventListener('click', (e)=>{
    e.stopPropagation();
    const ok = confirm(`Excluir sua avaliação de "${model.game}"? Esta ação não pode ser desfeita.`);
    if (ok) {
      const base = (window.CAMINHO_VIEWS || './');
      window.location.href = base + 'DeletarAvaliacao.php?id=' + encodeURIComponent(model.id);
    }
  });

  // clique no card abre a página do jogo
  root.addEventListener('click', ()=> window.open(title.href, '_blank', 'noopener'));

  return tpl;
}

/* Popula a lista */
function loadReviews(){
  const list = $('#reviewList');
  list.innerHTML = '';
  const data = ReviewsStore.all();
  if (!data.length) {
    list.innerHTML = '<p class="muted" style="padding:1rem">Você ainda não avaliou nenhum jogo.</p>';
    return;
  }
  data.forEach(r => list.appendChild(renderReviewCard(r)));
}

/* Init */
loadReviews();
</script>

</body>
</html>
