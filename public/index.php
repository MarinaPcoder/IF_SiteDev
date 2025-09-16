<?php
// public/index.php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\DB\Conexao;

// Mantido do seu arquivo original
const CAMINHO_VIEWS = './../App/Views/';

// ===== Conexão =====
$pdo = Conexao::getInstancia();
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

/**
 * Busca jogos com poster, gêneros e (se houver) média de notas / total de avaliações.
 * Alinhado ao seu esquema:
 *  - Tabelas: Jogo, Genero, Jogo_Genero, Avaliacao, Jogo_Imagem
 *  - Coluna do gênero: Genero.nome_genero
 *  - Na HOME usamos apenas o POSTER (banner é só na página específica do jogo)
 */
function fetchGames(\PDO $pdo): array {
    $sql = "
        SELECT
            j.id_jogo                                   AS id,
            j.titulo                                    AS title,
            j.descricao                                 AS descricao,
            j.desenvolvedora                            AS desenvolvedora,
            j.data_lancamento                           AS data_lancamento,
            COALESCE(p.caminho, 'assets/img/poster.png') AS poster,
            GROUP_CONCAT(DISTINCT g.nome_genero ORDER BY g.nome_genero SEPARATOR ' • ') AS genres_str,
            ROUND(AVG(a.nota), 1)                       AS rating,
            COUNT(a.id_avaliacao)                       AS votes
        FROM Jogo j
        LEFT JOIN Jogo_Imagem p  ON p.id_jogo = j.id_jogo AND p.tipo = 'poster'
        LEFT JOIN Jogo_Genero jg ON jg.id_jogo = j.id_jogo
        LEFT JOIN Genero g       ON g.id_genero = jg.id_genero
        LEFT JOIN Avaliacao a    ON a.id_jogo = j.id_jogo
        GROUP BY j.id_jogo
        ORDER BY COALESCE(ROUND(AVG(a.nota),1),0) DESC, j.id_jogo DESC
        LIMIT 100
    ";

    $rows = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

    // Base para normalizar caminhos relativos respeitando subpasta (ex.: /IFsitedev/public)
    $basePath   = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $basePrefix = $basePath === '' ? '' : $basePath;
    $toWeb = function (?string $p) use ($basePrefix): string {
        $p = (string)($p ?? '');
        if ($p === '') return '';
        if (preg_match('#^https?://#i', $p)) return $p; // externo
        $p = ltrim($p, '/');                              // remove barra inicial problemática
        return ($basePrefix ? $basePrefix.'/' : '/') . $p;
    };

    $games = [];
    foreach ($rows as $r) {
        $genresStr = (string)($r['genres_str'] ?? '');
        $genresArr = $genresStr !== '' ? array_map('trim', explode('•', $genresStr)) : [];

        $rating = is_null($r['rating']) ? 0.0 : (float)$r['rating'];
        $votes  = (int)($r['votes'] ?? 0);

        $release = '';
        if (!empty($r['data_lancamento']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $r['data_lancamento'])) {
            $dt = \DateTime::createFromFormat('Y-m-d', $r['data_lancamento']);
            if ($dt) $release = $dt->format('d/m/Y');
        }

        $desc = trim((string)($r['descricao'] ?? ''));
        if ($desc !== '' && mb_strlen($desc) > 220) {
            $desc = mb_substr($desc, 0, 220) . '…';
        }

        $games[] = [
            'id'      => (int)$r['id'],
            'title'   => (string)$r['title'],
            'year'    => $release ? (int)substr($r['data_lancamento'], 0, 4) : null,
            'release' => $release ?: '—',
            'rating'  => $rating,
            'votes'   => $votes,
            'genres'  => $genresArr,
            // HOME usa poster
            'cover'   => $toWeb($r['poster'] ?? 'assets/img/poster.png'),
            'desc'    => ($desc !== '' ? $desc : 'Sem descrição disponível.')
        ];
    }

    return $games;
}

$games = fetchGames($pdo);
$featured = $games[0] ?? null;

// Mensagem de sessão (se houver)
$flash = $_SESSION['Mensagem_redirecionamento'] ?? null;
unset($_SESSION['Mensagem_redirecionamento']);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Storm — Homepage</title>
  <link rel="stylesheet" href="./assets/css/index.css" />
  <meta name="color-scheme" content="dark light" />
  <link rel="icon" href="./assets/Favicon/logo-sem-fundo.png">
</head>
<body>
<?php if ($flash): ?>
  <div class="toast toast--info" role="status" aria-live="polite" style="position:fixed;top:12px;left:50%;transform:translateX(-50%);z-index:9999;background:#222;color:#fff;padding:.6rem 1rem;border-radius:.5rem;box-shadow:0 6px 20px rgba(0,0,0,.35)">
    <?= htmlspecialchars($flash, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') ?>
  </div>
  <script>setTimeout(()=>document.querySelector('.toast')?.remove(), 4200);</script>
<?php endif; ?>

<div id="app" class="app" aria-live="polite">

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

      <button id="toggleSidebar" class="btn btn--icon" title="Expandir/Recolher menu" aria-expanded="false" aria-controls="sidebar">
        <span class="sr-only">Alternar sidebar</span>⟷
      </button>
    </div>

    <nav class="nav">
      <div class="nav__group">
        <h6 class="nav__heading label">Menu</h6>

        <a class="nav__item active" href="index.php">
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
  <main id="main" class="main" tabindex="-1">

    <!-- Banner destaque (JOGOS) -->
    <section id="banner" class="banner reveal" aria-label="Destaque do jogo">
      <figure class="banner__media">
        <!-- HOME usa POSTER -->
        <img
          id="bannerPoster"
          class="banner__poster"
          src="<?= htmlspecialchars($featured['cover'] ?? './assets/img/poster.png', ENT_QUOTES) ?>"
          alt="Capa do jogo em destaque" />
        <figcaption class="sr-only" id="bannerCaption">Capa do jogo em destaque</figcaption>
      </figure>

      <div class="banner__meta">
        <div id="bannerRating" class="badge" aria-label="Nota Storm">
          <?php
            $fr = $featured;
            $r = isset($fr['rating']) ? number_format((float)$fr['rating'], 1, ',', '') : '0,0';
            $v = isset($fr['votes']) ? (int)$fr['votes'] : 0;
            echo "⭐ {$r} • " . number_format($v, 0, ',', '.') . " voto" . ($v===1 ? '' : 's');
          ?>
        </div>

        <header>
          <h1 id="bannerTitle" class="banner__title"><?= htmlspecialchars($featured['title'] ?? 'Sem jogos cadastrados', ENT_QUOTES) ?></h1>
          <p id="bannerGenres" class="banner__genres">
            <?= isset($featured['genres']) && $featured['genres']
                ? htmlspecialchars(implode(' • ', $featured['genres']), ENT_QUOTES)
                : '—' ?>
          </p>
        </header>

        <p id="bannerDesc" class="banner__desc">
          <?= htmlspecialchars($featured['desc'] ?? 'Cadastre jogos para ver destaques por aqui.', ENT_QUOTES) ?>
        </p>

        <div class="banner__cta">
          <?php if ($featured): ?>
            <a id="bannerEvalBtn" class="btn btn--primary" href="aval-jogo.php?id=<?= (int)$featured['id'] ?>">Ver detalhes e avaliar</a>
          <?php else: ?>
            <a class="btn btn--primary" href="<?= CAMINHO_VIEWS ?>CadastrarJogo.php">Cadastrar primeiro jogo</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="banner__bg-decor" aria-hidden="true"></div>
    </section>

    <!-- Carrossel dos MAIS POPULARES -->
    <section class="section reveal" aria-label="Mais populares">
      <header class="section__header">
        <h2>Mais populares</h2>
        <div class="section__controls">
          <button class="btn btn--icon" id="recentPrev" aria-label="Voltar">◀</button>
          <button class="btn btn--icon" id="recentNext" aria-label="Avançar">▶</button>
        </div>
      </header>

      <div id="railRecentes" class="rail" role="list" tabindex="0" aria-roledescription="carrossel">
        <!-- JS injeta cards -->
      </div>
    </section>

    <!-- Destaques & Lançamentos -->
    <section class="section reveal" aria-label="Destaques & Lançamentos">
      <header class="section__header">
        <h2>Destaques & Lançamentos</h2>
        <p class="muted">Cards largos com mini-avaliação e data de lançamento</p>
      </header>

      <div id="railBanners" class="rail rail--wide" role="list" tabindex="0">
        <!-- JS injeta banners de jogos (usando POSTER na HOME) -->
      </div>
    </section>
  </main>

  <!-- ============ LATERAL DIREITA ============ -->
  <aside class="aside" aria-label="Utilidades">
    <!-- Busca -->
    <section class="panel reveal" aria-label="Busca">
      <div class="search">
        <span class="search__icon" aria-hidden="true">🔎</span>
        <input id="searchInput" class="search__input" type="search" placeholder="Pesquisar jogos..." autocomplete="off" aria-label="Pesquisar jogos" />
        <button id="clearSearch" class="btn btn--icon" aria-label="Limpar busca">✕</button>
      </div>
      <div id="searchResults" class="search__results" role="listbox" aria-label="Resultados da busca"></div>
    </section>

    <!-- Mais avaliados (proxy: top por votos) -->
    <section class="panel reveal popular" aria-label="Mais avaliados">
      <header class="section__header section__header--tight">
        <h2>Mais avaliados</h2>
      </header>
      <div id="popularList" class="popular__list" role="list"></div>
    </section>
  </aside>
</div>

<!-- ======= TEMPLATES ======= -->
<template id="tpl-card-game">
  <article class="card" role="listitem" tabindex="0">
    <div class="card__media">
      <img class="card__img" alt="" loading="lazy" />
      <button class="card__fav btn btn--icon" title="Salvar" aria-label="Salvar">＋</button>
    </div>
    <div class="card__body">
      <header class="card__header">
        <h3 class="card__title"></h3>
        <span class="imdb"><b>Storm</b> <i class="imdb__score">0.0</i></span>
      </header>
      <p class="card__sub muted"></p>
    </div>
    <button class="card__action btn btn--ghost">Detalhes</button>
  </article>
</template>

<template id="tpl-popular-item">
  <button class="popular__item" role="listitem">
    <img class="popular__thumb" alt="" width="48" height="64" loading="lazy" />
    <div class="popular__meta">
      <div class="popular__title"></div>
      <div class="popular__note muted"><span class="search-count">0</span> votos</div>
    </div>
    <span class="popular__chev" aria-hidden="true">›</span>
  </button>
</template>

<template id="tpl-wide-banner">
  <article class="gamewide" role="listitem" tabindex="0">
    <!-- HOME usa POSTER aqui também -->
    <img class="gamewide__img" alt="" loading="lazy" />
    <div class="gamewide__meta">
      <h3 class="gamewide__title"></h3>
      <p class="gamewide__extra"><span class="mini-score">0.0</span> • <span class="release"></span></p>
      <a class="btn btn--tiny gamewide__btn" target="_blank" rel="noopener">Detalhes</a>
    </div>
  </article>
</template>

<!-- ======= DADOS INJETADOS DO BACK-END ======= -->
<script>
  window.STORM_DATA = <?= json_encode($games, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>

<!-- ======= JAVASCRIPT ======= -->
<script>
/* ============================== Utils ============================== */
const $  = (s,el=document)=>el.querySelector(s);
const $$ = (s,el=document)=>[...el.querySelectorAll(s)];
const fmt = (n)=>new Intl.NumberFormat('pt-BR').format(n);
const storage = {
  get(k,f=null){ try{const v=localStorage.getItem(k); return v?JSON.parse(v):f;}catch{return f;} },
  set(k,v){ try{localStorage.setItem(k,JSON.stringify(v));}catch{} }
};

/* ============= Tema & Sidebar =============== */
const ThemeManager=(()=>{const r=document.documentElement,t=$('#themeToggle'),k='storm:theme';
  function apply(x){r.setAttribute('data-theme',x); if(t) t.checked=(x==='dark'); storage.set(k,x);}
  function init(){const s=storage.get(k)||'dark'; apply(s); if(t) t.addEventListener('change',()=>apply(t.checked?'dark':'light'));}
  return{init};
})();
const Sidebar=(()=>{const el=$('#sidebar'),btn=$('#toggleSidebar'),k='storm:sidebar',pref=()=>innerWidth>760;
  function set(e){el.classList.toggle('expanded',e);el.classList.toggle('compact',!e);btn&&btn.setAttribute('aria-expanded',String(e));storage.set(k,e?'expanded':'compact');}
  function init(){const s=storage.get(k); set(s?s==='expanded':pref()); btn&&btn.addEventListener('click',()=>set(!el.classList.contains('expanded')));
    addEventListener('resize',()=>{const t=pref(); if(t&&el.classList.contains('compact'))set(true); if(!t&&el.classList.contains('expanded'))set(false);});}
  return{init};
})();

/* ============================== Data (BACK-END) ============================== */
const DataStore=(()=>{
  const G = Array.isArray(window.STORM_DATA) ? window.STORM_DATA : [];
  const norm = g => ({
    id: g.id,
    title: g.title,
    year: g.year ?? null,
    release: g.release ?? '—',
    rating: typeof g.rating==='number' ? g.rating : 0,
    votes: typeof g.votes==='number' ? g.votes : 0,
    genres: Array.isArray(g.genres) ? g.genres : [],
    cover: g.cover || 'assets/img/poster.png', // HOME usa poster
    desc: g.desc || 'Sem descrição disponível.'
  });
  const ALL = G.map(norm);
  return{
    all(){return [...ALL];},
    topByRating(n=12){return [...ALL].sort((a,b)=> (b.rating-a.rating) || (b.votes-a.votes)).slice(0,n);},
    topByVotes(n=6){return [...ALL].sort((a,b)=> (b.votes-a.votes) || (b.rating-a.rating)).slice(0,n);},
    byQuery(q){q=(q||'').trim().toLowerCase();
      if(!q) return [...ALL];
      return ALL.filter(g=> g.title.toLowerCase().includes(q) || g.genres.join(' ').toLowerCase().includes(q));
    },
    getFirst(){return ALL[0] || null;}
  };
})();

/* =============== Banner =================== */
const Banner=(()=>{
  const poster=$('#bannerPoster'),title=$('#bannerTitle'),genres=$('#bannerGenres'),
        desc=$('#bannerDesc'),rating=$('#bannerRating'),btn=$('#bannerEvalBtn');
  function set(game){
    if(!game) return;
    poster.src = game.cover; poster.alt=`Capa do jogo ${game.title}`;
    title.textContent=game.title;
    genres.textContent=game.genres.length?game.genres.join(' • '):'—';
    desc.textContent=game.desc;
    rating.textContent=`⭐ ${game.rating.toFixed(1)} • ${fmt(game.votes)} voto${game.votes===1?'':'s'}`;
    if(btn){ btn.href = `aval-jogo.php?id=${encodeURIComponent(game.id)}`; }
  }
  function init(){
    const first = DataStore.getFirst();
    if(first) set(first);
  }
  return{init,set};
})();

/* ============== Carrossel genérico ============== */
function makeRail(railEl, items, renderer){
  railEl.innerHTML=''; items.forEach(it=>railEl.appendChild(renderer(it)));
  railEl.addEventListener('wheel',e=>{ if(Math.abs(e.deltaY)>Math.abs(e.deltaX)){ railEl.scrollBy({left:e.deltaY*.7,behavior:'smooth'}); e.preventDefault(); } },{passive:false});
  return railEl;
}

/* ================= Cards de jogo =============== */
function renderGameCard(game){
  const tpl=$('#tpl-card-game').content.cloneNode(true);
  const root=tpl.querySelector('.card');
  const img =tpl.querySelector('.card__img');
  const title=tpl.querySelector('.card__title');
  const sub =tpl.querySelector('.card__sub');
  const score=tpl.querySelector('.imdb__score');

  img.src=game.cover; img.alt=`Capa: ${game.title}`;
  title.textContent=game.title;
  sub.textContent=game.genres.length?game.genres.join(' • '):'—';
  score.textContent=game.rating.toFixed(1);

  root.addEventListener('click',()=>Banner.set(game));
  root.querySelector('.card__fav').addEventListener('click',e=>{
    e.stopPropagation(); root.classList.toggle('is-faved'); root.setAttribute('aria-pressed',root.classList.contains('is-faved'));
  });
  root.querySelector('.card__action').addEventListener('click',e=>{
    e.stopPropagation();
    window.open(`aval-jogo.php?id=${encodeURIComponent(game.id)}`,'_blank','noopener');
  });
  return tpl;
}

/* ======= Banners largos (lançamentos) ============ */
function renderWideBanner(game){
  const tpl=$('#tpl-wide-banner').content.cloneNode(true);
  // HOME: usa POSTER
  tpl.querySelector('.gamewide__img').src=game.cover;
  tpl.querySelector('.gamewide__img').alt=`Arte: ${game.title}`;
  tpl.querySelector('.gamewide__title').textContent=game.title;
  tpl.querySelector('.mini-score').textContent=game.rating.toFixed(1);
  tpl.querySelector('.release').textContent=`Lançamento: ${game.release}`;
  const btn=tpl.querySelector('.gamewide__btn');
  btn.textContent='Detalhes'; btn.href=`aval-jogo.php?id=${encodeURIComponent(game.id)}`;
  return tpl;
}

/* ============= Mais pesquisados (proxy: top por votos) =========== */
function renderPopularItem(game){
  const tpl=$('#tpl-popular-item').content.cloneNode(true);
  tpl.querySelector('.popular__thumb').src=game.cover;
  tpl.querySelector('.popular__thumb').alt=`Capa mini: ${game.title}`;
  tpl.querySelector('.popular__title').textContent=game.title;
  tpl.querySelector('.search-count').textContent=fmt(game.votes);
  tpl.querySelector('.popular__item').addEventListener('click',()=>Banner.set(game));
  return tpl;
}

/* ============== Busca ====================== */
const Search=(()=>{
  const input=$('#searchInput'), clear=$('#clearSearch'), results=$('#searchResults'),
        rail=$('#railRecentes'), asideList=$('#popularList');
  function renderResults(q){
    const data=DataStore.byQuery(q).slice(0,6);
    results.innerHTML='';
    data.forEach(g=>{
      const b=document.createElement('button');
      b.className='search__item'; b.setAttribute('role','option');
      b.innerHTML=`<img src="${g.cover}" alt="" width="36" height="52" loading="lazy"/><span>${g.title}</span><em class="muted">${g.genres[0]||''}</em>`;
      b.addEventListener('click',()=>{ Banner.set(g); input.value=g.title; apply(q); results.hidden=true; });
      results.appendChild(b);
    });
    results.hidden=data.length===0||!q;
  }
  function apply(q){
    const filtered=DataStore.byQuery(q);
    rail.innerHTML=''; filtered.sort((a,b)=>b.rating-a.rating).forEach(g=>rail.appendChild(renderGameCard(g)));
    asideList.innerHTML=''; DataStore.topByVotes(6).forEach(g=>asideList.appendChild(renderPopularItem(g)));
  }
  function init(){
    input.addEventListener('input',e=>{const q=e.target.value; renderResults(q); apply(q);});
    clear.addEventListener('click',()=>{input.value=''; input.focus(); renderResults(''); apply('');});
    input.addEventListener('keydown',e=>{ if(e.key==='Escape'){ clear.click(); results.hidden=true; }});
    document.addEventListener('click',e=>{ if(!results.contains(e.target)&&e.target!==input) results.hidden=true; });
  }
  return{init,apply};
})();

/* ======== FX: reveal + teclado no rail =========== */
const FX=(()=>{
  function revealOnScroll(){
    const io=new IntersectionObserver(es=>es.forEach(en=>{ if(en.isIntersecting){ en.target.classList.add('show'); io.unobserve(en.target);} }),{threshold:.12});
    $$('.reveal').forEach(el=>io.observe(el));
  }
  function keyScroll(el){
    el.addEventListener('keydown',e=>{
      if(e.key==='ArrowRight') el.scrollBy({left:220,behavior:'smooth'});
      if(e.key==='ArrowLeft')  el.scrollBy({left:-220,behavior:'smooth'});
    });
  }
  return{revealOnScroll,keyScroll};
})();

/* ================= Init ====================== */
(function initStorm(){
  ThemeManager.init();
  Sidebar.init();
  Banner.init();

  // Carrossel: mais populares por nota (desempate por votos)
  const railRecentes=$('#railRecentes');
  const allTop = DataStore.topByRating(20);
  if(allTop.length){
    allTop.forEach(g=>railRecentes.appendChild(renderGameCard(g)));
  } else {
    railRecentes.innerHTML = '<p class="muted" style="padding:1rem">Sem jogos cadastrados ainda.</p>';
  }
  $('#recentPrev').addEventListener('click',()=>railRecentes.scrollBy({left:-400,behavior:'smooth'}));
  $('#recentNext').addEventListener('click',()=>railRecentes.scrollBy({left: 400,behavior:'smooth'}));

  // Lançamentos & destaques (HOME usa poster)
  const railBanners=$('#railBanners');
  const all = DataStore.all();
  if(all.length){
    all.forEach(g=>railBanners.appendChild(renderWideBanner(g)));
  } else {
    railBanners.innerHTML = '<p class="muted" style="padding:1rem">Cadastre jogos para ver destaques aqui.</p>';
  }

  // Mais pesquisados (proxy por votos)
  const popularList=$('#popularList');
  DataStore.topByVotes(6).forEach(g=>popularList.appendChild(renderPopularItem(g)));

  // Busca
  Search.init();

  // FX
  FX.revealOnScroll();
  FX.keyScroll(railRecentes);
  FX.keyScroll(railBanners);
})();
</script>

<!-- região de live-messages para A11y -->
<div class="sr-only" aria-live="assertive" id="sr-live"></div>
</body>
</html>
