<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Storm — Perfil do Usuário</title>
  <meta name="color-scheme" content="dark light" />
  <link rel="stylesheet" href="../css/perfil.css" />
  <link rel="icon" href="../Favicon/logo-sem-fundo.png" />
</head>
<body>

<div class="app" aria-live="polite">
  <!-- ============ SIDEBAR ============ -->
  <aside id="sidebar" class="sidebar compact" aria-label="Navegação principal">
    <div class="brand">
      <!-- Logo circular -->
      <a class="brand__avatar" href="index.html" aria-label="Storm — Homepage">
        <img id="siteLogo" src="../Favicon/logo-sem-fundo.png" alt="Logo Storm"
             onerror="this.replaceWith(this.nextElementSibling)" />
        <svg class="brand__avatar-fallback" viewBox="0 0 48 48" aria-hidden="true">
          <circle cx="24" cy="24" r="23" fill="none" stroke="currentColor" stroke-width="2"/>
          <path d="M18 30 30 8l-4 10h8L22 40l4-10z" fill="currentColor"/>
        </svg>
      </a>

      <a href=".html" class="brand__title-wrap">
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

        <a class="nav__item" href="index.html">
          <span class="nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22"><path d="M12 3 3 11h2v8a2 2 0 0 0 2 2h4v-6h2v6h4a2 2 0 0 0 2-2v-8h2L12 3z"/></svg>
          </span>
          <span class="label">Homepage</span>
        </a>

        <a class="nav__item" href="suges.html">
          <span class="nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22"><path d="M12 2a7 7 0 0 1 4 12c-.7.6-1 1.1-1 2v1H9v-1c0-.9-.3-1.4-1-2A7 7 0 0 1 12 2zm-3 17h6v2H9v-2z"/></svg>
          </span>
          <span class="label">Sugestões de Jogos</span>
        </a>
      </div>

      <div class="nav__group">
        <h6 class="nav__heading label">Social</h6>

        <a class="nav__item active" href="perfil.html">
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
  <main id="main" class="profile" tabindex="-1">
    <!-- cabeçalho opcional do perfil -->
    <header class="profile__header">
      <h1 class="profile__title">Minhas Avaliações</h1>
      <div class="profile__actions">
      </div>
    </header>

    <!-- lista de avaliações -->
    <section class="reviews" aria-label="Lista de avaliações do usuário">
      <div id="reviewList" class="reviews__list" role="list" tabindex="0">
        <!-- JS injeta <article class="review-card"> -->
      </div>

      <!-- paginação simples (opcional) -->
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
      <div class="usercard__cover" aria-hidden="true">
        <!-- Imagem de capa opcional via CSS -->
      </div>

      <div class="usercard__header">
        <img class="usercard__avatar" id="uc-avatar" src="https://i.pravatar.cc/200?img=5" alt="Foto de perfil" />
        <div class="usercard__id">
          <h2 id="uc-name" class="usercard__name">Marina Prado</h2>
          <div class="usercard__user">@marinaprado</div>
        </div>
      </div>

      <p class="usercard__bio">Amante de RPGs e jogos indies</p>

      <ul class="usercard__meta">
        <li>
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
          </span>
          <span id="uc-loc">São Paulo, BR</span>
        </li>
        <li>
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M7 4h10v2H7V4zm-3 4h16v2H4V8zm3 4h10v2H7v-2zm-3 4h16v2H4v-2z"/></svg>
          </span>
          <span id="uc-since">Membro desde jan/2023</span>
        </li>
        <li>
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 4h18v2H3V4zm2 4h14l-1 12H6L5 8zm5 2v8h2v-8H10zm-3 0 1 8h2l-1-8H7zm8 0-1 8h2l1-8h-2z"/></svg>
          </span>
          <span id="uc-count">54 avaliações</span>
        </li>
      </ul>

      <div class="usercard__actions">
        <button class="btn btn--primary" id="btnEditProfile">Editar Perfil</button>
        <button class="btn btn--ghost" id="btnLogout">Sair da conta atual</button>
        <button class="btn btn--danger" id="btnDelete">Excluir conta</button>
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

/* Mock de avaliações do usuário (substituir por API) */
const ReviewsStore = (()=>{
  const items = [
    {
      id: 1,
      game: "Hollow Knight",
      cover: "https://images.igdb.com/igdb/image/upload/t_cover_big/co1n23.jpg",
      rating: 9.0,
      date: "2024-07-18",
      comment: "Level design impecável, trilha sonora hipnótica e uma curva de desafio justa. O mapa poderia ser mais acessível no começo."
    },
    {
      id: 2,
      game: "Elden Ring",
      cover: "https://images.igdb.com/igdb/image/upload/t_cover_big/co49x5.jpg",
      rating: 9.3,
      date: "2024-06-02",
      comment: "Exploração que recompensa curiosidade. Combates memoráveis e builds muito variadas."
    },
    {
      id: 3,
      game: "Baldur's Gate 3",
      cover: "https://images.igdb.com/igdb/image/upload/t_cover_big/co6f9k.jpg",
      rating: 9.6,
      date: "2024-08-05",
      comment: "Roleplay absurdo de bom. Quase tudo tem consequência. Melhor campanha de D&D digital."
    }
  ];
  return {
    all(){ return [...items]; },
    remove(id){ const i=items.findIndex(x=>x.id===id); if(i>=0) items.splice(i,1); },
    get(id){ return items.find(x=>x.id===id); },
    update(id, patch){ const it=this.get(id); if(it) Object.assign(it, patch); }
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
  title.href = `aval-jogo.html?jogo=${encodeURIComponent(model.game)}`;

  score.textContent = `⭐ ${Number(model.rating).toFixed(1)}/10`;
  date.textContent  = fmtDate(model.date);
  date.dateTime     = model.date;
  text.textContent  = model.comment;

  // ações
  tpl.querySelector('.edit').addEventListener('click', (e)=>{
    e.stopPropagation();
    const newRating = prompt(`Nova nota para "${model.game}" (0-10):`, model.rating);
    if (newRating === null) return;
    const val = Math.max(0, Math.min(10, parseFloat(newRating)));
    ReviewsStore.update(model.id, { rating: val });
    score.textContent = `⭐ ${val.toFixed(1)}/10`;
  });

  tpl.querySelector('.delete').addEventListener('click', (e)=>{
    e.stopPropagation();
    if (confirm(`Excluir sua avaliação de "${model.game}"? Esta ação não pode ser desfeita.`)) {
      ReviewsStore.remove(model.id);
      root.remove();
      updateCounters();
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
  ReviewsStore.all().forEach(r => list.appendChild(renderReviewCard(r)));
  updateCounters();
}

/* Painel do usuário: atualiza o total */
function updateCounters(){
  const total = ReviewsStore.all().length;
  $('#uc-count').textContent = `${total} avaliação${total===1?'':'es'}`;
}

/* Botões do cabeçalho / usercard */
$('#btnNew')?.addEventListener('click', ()=>{
  alert('A criação de avaliação direta será implementada. Por enquanto, use a página do jogo para avaliar.');
});
$('#btnExport')?.addEventListener('click', ()=>{
  const data = JSON.stringify(ReviewsStore.all(), null, 2);
  const blob = new Blob([data], {type:'application/json'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = 'storm-minhas-avaliacoes.json'; a.click();
  URL.revokeObjectURL(url);
});
$('#btnEditProfile')?.addEventListener('click', ()=> alert('Tela de edição de perfil em construção.'));
$('#btnLogout')?.addEventListener('click', ()=> alert('Você saiu da conta. (mock)'));
$('#btnDelete')?.addEventListener('click', ()=>{
  if (confirm('Tem certeza que deseja excluir sua conta? Esta ação é irreversível.')) {
    alert('Conta excluída. (mock)');
  }
});

/* Init */
loadReviews();
</script>

</body>
</html>
