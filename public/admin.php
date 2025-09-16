<?php
// public/admin.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\DB\Conexao;

// Caminho para as views (formularios CRUD etc.)
const CAMINHO_VIEWS = './../App/Views/';

// ============================ Guard de autenticação ============================
if (empty($_SESSION['Usuario']['Id'])) {
  header('Location: ' . CAMINHO_VIEWS . 'loginUsuario.php');
  exit;
}

$pdo = Conexao::getInstancia();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$userId = (int) $_SESSION['Usuario']['Id'];
$stmtInfo = $pdo->prepare("SELECT nome_usuario, tipo_perfil FROM Usuario WHERE id_usuario = :id LIMIT 1");
$stmtInfo->execute([':id' => $userId]);
$me = $stmtInfo->fetch(PDO::FETCH_ASSOC) ?: ['nome_usuario'=>'', 'tipo_perfil'=>'usuario'];

if (($me['tipo_perfil'] ?? 'usuario') !== 'admin') {
  // Só admins!
  header('Location: ./index.php');
  exit;
}

// ================================ AJAX (reviews) ==============================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'reviews') {
  header('Content-Type: application/json; charset=utf-8');

  $q = trim((string)($_GET['q'] ?? ''));
  $bind = [];
  $where = '';

  if ($q !== '') {
    $where = " AND (
      j.titulo LIKE :q OR
      u.nome_usuario LIKE :q OR
      a.justificativa LIKE :q
    )";
    $bind[':q'] = '%' . $q . '%';
  }

  $sqlPoster = "
    SELECT ji.id_jogo, ji.caminho
    FROM Jogo_Imagem ji
    WHERE ji.tipo = 'poster'
    GROUP BY ji.id_jogo
  ";

  $sql = "
    SELECT 
      a.id_avaliacao AS id,
      a.nota,
      a.justificativa,
      a.data_avaliacao,
      j.id_jogo,
      j.titulo AS jogo,
      u.nome_usuario AS usuario,
      COALESCE(p.caminho, '/assets/img/poster.png') AS poster
    FROM Avaliacao a
    INNER JOIN Usuario u ON u.id_usuario = a.id_usuario
    INNER JOIN Jogo j     ON j.id_jogo    = a.id_jogo
    LEFT JOIN ($sqlPoster) p ON p.id_jogo = j.id_jogo
    WHERE 1=1 $where
    ORDER BY a.data_avaliacao DESC, a.id_avaliacao DESC
    LIMIT 200
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($bind);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // normaliza paths web (subpasta)
  $basePath   = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
  $prefix     = $basePath ? $basePath.'/' : '/';
  foreach ($rows as &$r) {
    $p = (string)($r['poster'] ?? '');
    if ($p !== '' && !preg_match('#^https?://#i', $p)) {
      $r['poster'] = $prefix . ltrim($p, '/');
    }
  }

  echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

// =============================== Dados dos jogos ==============================
$sqlPoster = "
  SELECT ji.id_jogo, ji.caminho
  FROM Jogo_Imagem ji
  WHERE ji.tipo = 'poster'
  GROUP BY ji.id_jogo
";

$sqlGames = "
  SELECT 
    j.id_jogo,
    j.titulo,
    j.plataforma,
    j.data_lancamento,
    COALESCE(p.caminho, '/assets/img/poster.png') AS poster,
    -- média e contagem
    (SELECT ROUND(AVG(a.nota),1) FROM Avaliacao a WHERE a.id_jogo = j.id_jogo)  AS media,
    (SELECT COUNT(1)           FROM Avaliacao a WHERE a.id_jogo = j.id_jogo)    AS reviews
  FROM Jogo j
  LEFT JOIN ($sqlPoster) p ON p.id_jogo = j.id_jogo
  ORDER BY j.id_jogo DESC
  LIMIT 100
";

$games = $pdo->query($sqlGames)->fetchAll(PDO::FETCH_ASSOC);

// normaliza caminho das imagens
$basePath   = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$prefix     = $basePath ? $basePath.'/' : '/';
foreach ($games as &$g) {
  $p = (string)($g['poster'] ?? '');
  if ($p !== '' && !preg_match('#^https?://#i', $p)) {
    $g['poster'] = $prefix . ltrim($p, '/');
  }
}
unset($g);

// helpers para HTML
function h(?string $s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Storm — Admin</title>
  <meta name="color-scheme" content="dark light" />
  <link rel="icon" href="./assets/Favicon/logo-sem-fundo.png">
  <style>
    :root{
      --bg:#0f141a;--panel:#151b22;--muted:#9aa4b2;--text:#e8eef6;--brand:#ffd21f;
      --btn:#2a3340;--btn-ghost:#2a3340;--btn-danger:#ff4d6d;--accent:#4da3ff;
      --chip:#222b36;--chip-text:#e8eef6;--border:#253142;
    }
    *{box-sizing:border-box}
    html,body{margin:0;height:100%;background:var(--bg);color:var(--text);font:16px/1.4 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial}
    a{color:#bcd7ff;text-decoration:none}
    a:hover{text-decoration:underline}
    .app{display:grid;grid-template-columns: 280px 1fr;min-height:100vh;gap:16px;padding:16px}
    /* Sidebar */
    .sidebar{background:var(--panel);border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:16px}
    .brand{display:flex;flex-direction:column;gap:10px}
    .brand__title{font-weight:700}
    .nav{display:flex;flex-direction:column;gap:8px}
    .nav__group{padding:8px 0}
    .nav__heading{font-size:.85rem;color:var(--muted);margin:0 0 6px}
    .nav__item{display:flex;align-items:center;gap:10px;padding:10px;border-radius:10px;color:var(--text);text-decoration:none}
    .nav__item:hover{background:#1b2330}
    .nav__item.active{background:#1b2330;outline:1px solid #2b3648}
    .badge{background:var(--chip);color:var(--chip-text);border-radius:999px;padding:.35rem .55rem;font-size:.85rem}

    /* Main */
    .main{display:flex;flex-direction:column;gap:16px}
    .page-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--panel);border-radius:14px}
    .page-title{margin:0;font-size:1.1rem}

    .grid{display:grid;gap:16px}
    .two-col{grid-template-columns: 1.2fr 1fr}
    @media (max-width: 1200px){ .app{grid-template-columns:1fr} .two-col{grid-template-columns:1fr} }

    /* Cards containers */
    .section{background:var(--panel);border-radius:14px;padding:16px}
    .section h2{margin:0 0 10px}
    .section .muted{color:var(--muted)}

    /* ====== Game Grid / Card ====== */
    .games-grid{display:grid;gap:14px;grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));}
    @media (max-width:1280px){ .games-grid{grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));} }
    @media (max-width:980px){ .games-grid{grid-template-columns: 1fr;} }

    .game-card{display:grid;grid-template-columns: 88px 1fr auto;gap:12px;padding:14px;border:1px solid var(--border);border-radius:12px;overflow:visible;background:#111820}
    .game-card__cover{width:88px;height:118px;border-radius:10px;object-fit:cover;background:#0b1016}
    .game-card__title{margin:0 0 4px;font-size:1.125rem;line-height:1.2}
    .game-card__meta{display:flex;flex-direction:column;gap:10px}
    .row{display:flex;flex-wrap:wrap;gap:.5rem .75rem;align-items:center}
    .chip{background:var(--chip);color:var(--chip-text);border-radius:999px;padding:.35rem .55rem;font-size:.85rem;display:inline-flex;align-items:center;gap:.4rem}
    .chip svg{width:16px;height:16px}

    .game-card__cta{display:flex;gap:.5rem;flex-wrap:wrap;align-content:flex-start;justify-content:flex-end}
    .game-card__actions{display:grid;grid-template-columns:repeat(auto-fit, minmax(140px,1fr));gap:.6rem;margin-top:.25rem;width:100%}
    .game-card__actions .btn{width:100%}

    /* Buttons */
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border:0;border-radius:10px;background:var(--btn);color:var(--text);padding:.55rem .85rem;cursor:pointer;white-space:nowrap}
    .btn:hover{filter:brightness(1.1)}
    .btn--ghost{background:#1c2431}
    .btn--danger{background:var(--btn-danger)}
    .btn--primary{background:linear-gradient(180deg,#ffd84b,#ffb800);color:#1b1300}
    .btn--tiny{padding:.35rem .55rem;border-radius:8px;font-size:.9rem}

    /* ====== Reviews ====== */
    .reviews-panel{display:flex;flex-direction:column;gap:12px}
    .search{display:flex;gap:.5rem}
    .search input{flex:1;border-radius:10px;border:1px solid var(--border);background:#0f151e;color:var(--text);padding:.6rem .75rem}
    .review-list{display:flex;flex-direction:column;gap:10px;max-height:720px;overflow:auto;padding-right:4px}
    .review-item{border:1px solid var(--border);border-radius:12px;padding:10px;display:grid;grid-template-columns: 64px 1fr auto;gap:10px;background:#111820}
    .review-item__poster{width:64px;height:86px;border-radius:8px;object-fit:cover;background:#0b1016}
    .review-item__title{margin:0 0 2px}
    .review-item__meta{color:var(--muted);font-size:.9rem}
    .review-item__text{margin:.35rem 0 0;color:#cfd9e6}
    .review-item__cta{display:flex;flex-direction:column;gap:.5rem;align-items:flex-end}
    @media (max-width:700px){ .review-item{grid-template-columns:48px 1fr} .review-item__cta{flex-direction:row;justify-content:flex-end} }

    /* Sidebar user info (rodapé da coluna) */
    .who{margin-top:auto;color:var(--muted);font-size:.9rem}
    .role{display:inline-block;border:1px solid #2b3648;border-radius:999px;padding:.15rem .5rem;margin-top:.3rem}

  </style>
</head>
<body>

<div class="app admin">
  <!-- ============ SIDEBAR ============ -->
  <aside class="sidebar" aria-label="Admin">
    <div class="brand">
      <div class="brand__title">Storm • <b>Admin</b></div>
    </div>

    <nav class="nav">
      <div class="nav__group">
        <div class="nav__heading">Navegação</div>
        <a class="nav__item active" href="./admin.php">Dashboard</a>
        <a class="nav__item" href="./index.php">Homepage</a>
        <a class="nav__item" href="./perfil.php">Meu perfil</a>
      </div>
      <div class="nav__group">
        <div class="nav__heading">Ações</div>
        <a class="nav__item" href="<?= CAMINHO_VIEWS ?>CadastrarJogo.php">➕ Cadastrar jogo</a>
      </div>
    </nav>

    <div class="who">
      Logado como <b><?= h($me['nome_usuario'] ?? '') ?></b> • perfil
      <div class="role"><?= h($me['tipo_perfil'] ?? 'usuario') ?></div>
    </div>
  </aside>

  <!-- ============ MAIN ============ -->
  <main class="main">
    <div class="page-header">
      <h1 class="page-title">Painel de Administração</h1>
      <a class="btn btn--primary" href="<?= CAMINHO_VIEWS ?>cadastroJogo.php">Novo Jogo</a>
    </div>

    <div class="grid two-col">
      <!-- ================== JOGOS ================== -->
      <section class="section">
        <h2>Jogos</h2>
        <p class="muted">Últimos cadastrados (máx. 100). Avaliações e média são calculadas do banco.</p>

        <div class="games-grid">
          <?php foreach ($games as $g): ?>
            <article class="game-card">
              <img class="game-card__cover" src="<?= h($g['poster']) ?>" alt="Poster de <?= h($g['titulo']) ?>" />

              <div class="game-card__meta">
                <h3 class="game-card__title"><a href="./aval-jogo.php?id=<?= (int)$g['id_jogo'] ?>"><?= h($g['titulo']) ?></a></h3>

                <div class="row">
                  <span class="chip" title="Plataforma">
                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M3 5h18v9H3zM2 16h8v2H2zm10 0h10v2H12z"/></svg>
                    <?= h(strtolower((string)$g['plataforma'])) ?>
                  </span>

                  <span class="chip" title="Lançamento">
                    Lançamento: <?= $g['data_lancamento'] ? date('d/m/Y', strtotime($g['data_lancamento'])) : '—' ?>
                  </span>

                  <span class="chip">Média: <b><?= $g['media'] !== null ? number_format((float)$g['media'], 1, ',', '.') : '—' ?></b></span>
                  <span class="chip">Reviews: <b><?= (int)$g['reviews'] ?></b></span>
                </div>

                <div class="game-card__actions">
                  <a class="btn btn--ghost" href="<?= CAMINHO_VIEWS ?>upload_form.php?id=<?= (int)$g['id_jogo'] ?>">Imagens</a>
                  <a class="btn btn--ghost" href="<?= CAMINHO_VIEWS ?>CadastrarAvaliacao.php?id_jogo=<?= (int)$g['id_jogo'] ?>">Nova avaliação</a>
                </div>
              </div>

              <div class="game-card__cta">
                <a class="btn btn--ghost"  href="<?= CAMINHO_VIEWS ?>AlterarJogo.php?id=<?= (int)$g['id_jogo'] ?>">Editar</a>
                <a class="btn btn--danger" href="<?= CAMINHO_VIEWS ?>DeletarJogo.php?id=<?= (int)$g['id_jogo'] ?>" onclick="return confirm('Excluir o jogo &quot;<?= h($g['titulo']) ?>&quot;? Essa ação é irreversível.');">Excluir</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- ================== AVALIAÇÕES ================== -->
      <section class="section">
        <h2>Avaliações (mais recentes)</h2>
        <div class="reviews-panel">
          <div class="search">
            <input id="q" type="search" placeholder="Buscar por jogo, usuário ou texto..." />
            <button class="btn" id="btnBuscar">Buscar</button>
          </div>
          <div id="revList" class="review-list" role="list">
            <div class="muted">Carregando…</div>
          </div>
        </div>
      </section>
    </div>
  </main>
</div>

<!-- =========================== JS =========================== -->
<script>
  const $ = (s, el=document)=>el.querySelector(s);
  const $$ = (s, el=document)=>[...el.querySelectorAll(s)];
  const fmtDate = (iso)=> new Date(iso).toLocaleString('pt-BR', {day:'2-digit',month:'2-digit',year:'numeric', hour:'2-digit', minute:'2-digit'});

  async function fetchReviews(q=''){
    const url = new URL(location.href);
    url.searchParams.set('ajax','reviews');
    if(q) url.searchParams.set('q', q); else url.searchParams.delete('q');

    const res = await fetch(url.toString(), {headers:{'X-Requested-With':'fetch'}});
    if(!res.ok) throw new Error('Falha ao carregar avaliações');
    return await res.json();
  }

  function renderReview(r){
    const wrap = document.createElement('article');
    wrap.className = 'review-item';
    wrap.setAttribute('role','listitem');

    wrap.innerHTML = `
      <img class="review-item__poster" src="${r.poster}" alt="Poster de ${r.jogo}"/>
      <div>
        <h4 class="review-item__title"><a href="./aval-jogo.php?id=${r.id_jogo}" target="_blank" rel="noopener">${r.jogo}</a></h4>
        <div class="review-item__meta">Por <b>${r.usuario}</b> • ⭐ <b>${Number(r.nota).toFixed(1)}</b> • <time datetime="${r.data_avaliacao}">${fmtDate(r.data_avaliacao)}</time></div>
        ${r.justificativa ? `<p class="review-item__text">${r.justificativa.replace(/</g,'&lt;')}</p>` : ''}
      </div>
      <div class="review-item__cta">
        <a class="btn btn--ghost btn--tiny" href="<?= CAMINHO_VIEWS ?>AlterarAvaliacao.php?id=${r.id}">Editar</a>
        <a class="btn btn--danger btn--tiny" href="<?= CAMINHO_VIEWS ?>DeletarAvaliacao.php?id=${r.id}" onclick="return confirm('Excluir esta avaliação? Esta ação é irreversível.');">Excluir</a>
      </div>
    `;
    return wrap;
  }

  async function load(q=''){
    const list = $('#revList');
    list.innerHTML = '<div class="muted">Carregando…</div>';
    try{
      const data = await fetchReviews(q);
      list.innerHTML = '';
      if(!data.length){
        list.innerHTML = '<div class="muted">Nenhuma avaliação encontrada.</div>';
        return;
      }
      data.forEach(item => list.appendChild(renderReview(item)));
    }catch(e){
      list.innerHTML = `<div class="muted">Erro: ${e.message}</div>`;
    }
  }

  // Busca
  $('#btnBuscar').addEventListener('click', ()=> load($('#q').value.trim()));
  $('#q').addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); load($('#q').value.trim()); } });

  // Inicial
  load();
</script>
</body>
</html>
