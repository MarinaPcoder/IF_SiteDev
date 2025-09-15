<?php 

    session_start();

    require_once '../../vendor/autoload.php';

    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }
    
    use App\Controllers\UsuarioController;
    use App\Controllers\JogoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;

      const CAMINHO_PUBLIC = './../../public/';
      const CAMINHO_INDEX = './../../public/index.php';

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    } else {
        [$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);
    
        if (!$logado) {
            $_SESSION['Mensagem_redirecionamento'] = "Usuario não existe ou não tem permissão. Redirecionado para ./logout.php";
            header(header: "Location: ./logout.php");
            exit;
        }
    }

    $titulo = 'Storm — Sugerir Jogo';
    require_once '../../public/assets/components/head.php';
    
?>
 <!-- configuração  Head -->
  <meta name="color-scheme" content="dark light" />
  <link rel="stylesheet" href="<?= CAMINHO_PUBLIC ?>assets/css/styles-suges.css">
  <link rel="icon" href="<?= CAMINHO_PUBLIC ?>assets/Favicon/logo-sem-fundo.png">
</head>

<?php

    $dadoUsuario = ($usuario -> getUsuario(id: $_SESSION['Usuario']['Id']))[0];

    $GLOBALS['erros'] = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Dados do formulário
            $para = "projetostormsugestoes@gmail.com";
            $gameTitle = $_POST['gameTitle'] ?? false;
            $corpo = $_POST['reason'] ?? false;
            $platforms = $_POST['platforms'] ?? [];
            $platformOther = $_POST['platformOther'] ?? '';
            $genero = $_POST['genre'] ?? false;
            $link = $_POST['link'] ?? false;
            $consent = $_POST['consent'] ?? false;

            // Validação simples
            if (empty($gameTitle)) {
                $GLOBALS['erros']['gameTitle'][] = "O campo título do jogo é obrigatório.";
            }
            if (empty($corpo)) {
                $GLOBALS['erros']['Mensagem'][] = "O campo mensagem é obrigatório.";
            }

            if (strlen($corpo) > 400) {
                $GLOBALS['erros']['Mensagem'][] = "O campo mensagem não pode ter mais de 400 caracteres.";
            }

            if (isset($_POST['nick']) && !empty(trim($_POST['nick']))) {
                $corpo .= " (Nome no crédito: " . trim($_POST['nick']) . ")";
            }

            if (!empty($platforms)) {
                $corpo .= " (Plataformas: " . implode(", ", $platforms) . ")";
            }

            if (isset($_POST['platformOther']) && !empty(trim($_POST['platformOther']))) {
                $corpo .= " (Outra plataforma: " . trim($_POST['platformOther']) . ")";
            }

            if (isset($_POST['genre']) && !empty(trim($_POST['genre']))) {
                $corpo .= " (Gênero: " . trim($_POST['genre']) . ")";
            }

            if (isset($_POST['link']) && !empty(trim($_POST['link']))) {
                $corpo .= " (Link: " . trim($_POST['link']) . ")";
            }

            $headers = "From:projetostormsugestoes@gmail.com";
                       
            if (isset($_POST['email']) && !empty(trim($_POST['email'])) && $consent) {
                $corpo .= " (E-mail para contato: " . trim($_POST['email']) . ")";
                $headers .= "\r\n" .
                       "Reply-To: " . trim($_POST['email']) . "\r\n";
            }

            if (empty($GLOBALS['erros'])) {
                // Enviar e-mail
                if (mail($para, $gameTitle, $corpo, $headers)) {
                    $_SESSION['Mensagem_redirecionamento'] = "E-mail enviado com sucesso.";
                    header("Location: " . CAMINHO_INDEX);
                    exit;
                } else {
                    $_SESSION['Mensagem_redirecionamento'] = "Falha ao enviar o e-mail.";
                    header("Location: " . CAMINHO_INDEX);
                    exit;
                }
            }
    }
?>

<body>

      <!-- ======= LAYOUT APP (sidebar + conteúdo) ======= -->
  <div class="app" aria-live="polite">
    <!-- ============ SIDEBAR ============ -->
    <aside id="sidebar" class="sidebar compact" aria-label="Navegação principal">
      <div class="brand">
        <!-- Avatar circular para LOGO -->
        <a class="brand__avatar" href="<?= CAMINHO_PUBLIC ?>index.php" aria-label="Storm — Homepage">
          <img id="siteLogo" src="../Favicon/logo-sem-fundo.png" alt="Logo Storm"
               onerror="this.replaceWith(this.nextElementSibling)" />
          <svg class="brand__avatar-fallback" viewBox="0 0 48 48" aria-hidden="true">
            <circle cx="24" cy="24" r="23" fill="none" stroke="currentColor" stroke-width="2"/>
            <path d="M18 30 30 8l-4 10h8L22 40l4-10z" fill="currentColor"/>
          </svg>
        </a>

        <a href="<?= CAMINHO_PUBLIC ?>index.php" class="brand__title-wrap">
          <strong class="brand__title label">Storm.</strong>
        </a>

        <!-- Botão expandir/recolher -->
        <button id="toggleSidebar" class="btn btn--icon" title="Expandir/Recolher menu"
                aria-expanded="false" aria-controls="sidebar">
          <span class="sr-only">Alternar sidebar</span>⟷
        </button>
      </div>

      <nav class="nav">
        <div class="nav__group">
          <h6 class="nav__heading label">Menu</h6>

          <!-- Homepage -->
          <a class="nav__item" href="<?= CAMINHO_PUBLIC ?>index.php">
            <span class="nav__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="22" height="22">
                <path d="M12 3 3 11h2v8a2 2 0 0 0 2 2h4v-6h2v6h4a2 2 0 0 0 2-2v-8h2L12 3z"/>
              </svg>
            </span>
            <span class="label">Homepage</span>
          </a>

          <!-- Sugestões (página atual) -->
          <a class="nav__item active" href="">
            <span class="nav__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="22" height="22">
                <path d="M12 2a7 7 0 0 1 4 12c-.7.6-1 1.1-1 2v1H9v-1c0-.9-.3-1.4-1-2A7 7 0 0 1 12 2zm-3 17h6v2H9v-2z"/>
              </svg>
            </span>
            <span class="label">Sugestões de Jogos</span>
          </a>
        </div>

        <div class="nav__group">
          <h6 class="nav__heading label">Social</h6>
          <a class="nav__item" href="<?= CAMINHO_PUBLIC ?>perfil.php">
            <span class="nav__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="22" height="22">
                <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/>
              </svg>
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

    <!-- ======== CONTEÚDO (FORMULÁRIO) ======== -->
    <main class="suges" id="main" tabindex="-1">
      <div class="bg-orbs" aria-hidden="true"></div>

      <section class="card" role="form" aria-labelledby="title">


        <h1 id="title" class="card__title">Sugerir um Jogo</h1>

        <!-- Formulário -->
        <form id="suggestForm" class="form" action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post" enctype="multipart/form-data">
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
          <!-- coluna esquerda -->
          <div class="form__grid">
            <div class="col col--main">
              <fieldset class="fieldset">
                <legend class="legend">Detalhes básicos</legend>

                <!-- Nome do jogo -->
                <label class="field">
                  <span class="field__label">Nome do Jogo <b title="obrigatório">*</b></span>
                  <input type="text" name="gameTitle" id="gameTitle" placeholder="Digite o nome exato do jogo" required value="<?= htmlspecialchars(string: $_POST['gameTitle'] ?? '') ?>"/>
                  <small class="hint">Ex.: “Hades”, “The Witcher 3: Wild Hunt”</small>
                </label>

                <!-- Plataformas -->
                <div class="field">
                  <span class="field__label">Plataforma(s) <b title="obrigatório">*</b></span>
                  <div class="checks checks--grid" role="group" aria-label="Plataformas">
                    <label class="check"><input type="checkbox" name="platforms[]" value="PC" required /><i></i><span>PC</span></label>
                    <label class="check"><input type="checkbox" name="platforms[]" value="PlayStation" /><i></i><span>PlayStation</span></label>
                    <label class="check"><input type="checkbox" name="platforms[]" value="Xbox" /><i></i><span>Xbox</span></label>
                    <label class="check"><input type="checkbox" name="platforms[]" value="Switch" /><i></i><span>Switch</span></label>
                    <label class="check"><input type="checkbox" name="platforms[]" value="Mobile" /><i></i><span>Mobile</span></label>
                    <label class="check"><input type="checkbox" name="platforms[]" value="Mac" /><i></i><span>Mac</span></label>
                    <label class="check"><input type="checkbox" name="platforms[]" value="Linux" /><i></i><span>Linux</span></label>
                    <label class="check check--other">
                      <input type="checkbox" id="platformOtherToggle" />
                      <i></i><span>Outro</span>
                    </label>
                    <input class="field field--inline" type="text" id="platformOther" name="platformOther" placeholder="Informe outra plataforma" disabled />
                  </div>
                </div>

                <!-- Gênero -->
                <label class="field">
                  <span class="field__label">Gênero <b title="obrigatório">*</b></span>
                  <select name="genre" id="genre" required>
                    <option value="" selected disabled>Selecione um gênero</option>
                    <option value="Ação e combate" <?=(isset($_POST['genre']) && $_POST['genre'] === 'Ação e combate') ? 'selected' : ''?>>Ação e combate</option>
                    <option value="Esportes e competição" <?=(isset($_POST['genre']) && $_POST['genre'] === 'Esportes e competição') ? 'selected' : ''?>>Esportes e competição</option>
                    <option value="Exploração e aventura" <?=(isset($_POST['genre']) && $_POST['genre'] === 'Exploração e aventura') ? 'selected' : ''?>>Exploração e aventura</option>
                    <option value="Música e partygames" <?=(isset($_POST['genre']) && $_POST['genre'] === 'Música e partygames') ? 'selected' : ''?>>Música e partygames</option>
                    <option value="Plataforma e indie" <?=(isset($_POST['genre']) && $_POST['genre'] === 'Plataforma e indie') ? 'selected' : ''?>>Plataforma e indie</option>
                    <option value="Simulação e construção" <?=(isset($_POST['genre']) && $_POST['genre'] === 'Simulação e construção') ? 'selected' : ''?>>Simulação e construção</option>
                    <option value="Terror e mistério" <?=(isset($_POST['genre']) && $_POST['genre'] === 'Terror e mistério') ? 'selected' : ''?>>Terror e mistério</option>
                  </select>
                </label>

                <!-- Motivo -->
                <label class="field">
                  <span class="field__label">Motivo da Sugestão <small class="muted">(opcional)</small></span>
                  <textarea name="reason" id="reason" rows="4" placeholder="Conte por que esse jogo merece entrar no Storm..."><?= htmlspecialchars(string: $_POST['reason'] ?? '') ?></textarea>
                  <small class="hint"><span id="reasonCount">0</span>/400</small>
                </label>

                <!-- Link -->
                <label class="field">
                  <span class="field__label">Link de Referência <small class="muted">(opcional)</small></span>
                  <input type="url" name="link" id="link" placeholder="https://store.steampowered.com/app/..." inputmode="url" />
                  <small class="hint">Pode ser Steam, Epic, trailer no YouTube, site oficial, etc.</small>
                </label>
              </fieldset>

            </div>

            <!-- coluna direita -->
            <div class="col col--side">
              <fieldset class="fieldset">
                <legend class="legend">Dados do usuário <small class="muted">(opcional)</small></legend>

                <label class="field">
                  <span class="field__label">Nome ou Apelido</span>
                  <input type="text" name="nick" id="nick" placeholder="Como quer aparecer no crédito?" value="<?= htmlspecialchars(string: $_POST['nick'] ?? $dadoUsuario['nome_usuario']) ?>"/>
                </label>

                <label class="field">
                  <span class="field__label">E-mail <small class="muted">(opcional)</small></span>
                  <input type="email" name="email" id="email" placeholder="voce@exemplo.com" disabled value="<?= htmlspecialchars(string: $_POST['email'] ?? $dadoUsuario['email']) ?>"/>
                </label>

                <label class="check check--consent">
                  <input name="consent" type="checkbox" id="consent" />
                  <i></i>
                  <span>Quero receber atualizações por e-mail sobre essa sugestão.</span>
                </label>
              </fieldset>
            </div>
          </div>

          <!-- Ações -->
          <footer class="form__actions">
            <button type="reset" class="btn btn--ghost">Limpar</button>
            <input type="submit" value="Enviar"  class="btn btn--primary"/>
          </footer>
        </form>
      </section>
    </main>
  </div>

  <!-- ======= JS ======= -->
  <script>
    /* Helpers */
    const $ = (s,el=document)=>el.querySelector(s);
    const storage = {
      get(k,f=null){ try{const v=localStorage.getItem(k); return v?JSON.parse(v):f;}catch{return f;} },
      set(k,v){ try{localStorage.setItem(k,JSON.stringify(v));}catch{} }
    };

    /* Tema (claro/escuro) */
    (function ThemeManager(){
      const root = document.documentElement;
      const toggle = document.getElementById('themeToggle');
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

    /* Sidebar expand/compact */
    (function Sidebar(){
      const el  = document.getElementById('sidebar');
      const btn = document.getElementById('toggleSidebar');
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

    /* ======= JS do formulário (igual ao seu) ======= */
    const toggleOther = document.getElementById('platformOtherToggle');
    const otherInput  = document.getElementById('platformOther');
    toggleOther?.addEventListener('change', () => {
      otherInput.disabled = !toggleOther.checked;
      if (!otherInput.disabled) otherInput.focus();
      if (!toggleOther.checked) otherInput.value = '';
    });

    const reason = document.getElementById('reason');
    const counter = document.getElementById('reasonCount');
    if (reason && counter) {
      const max = 400; reason.maxLength = max;
      const upd = () => counter.textContent = reason.value.length;
      reason.addEventListener('input', upd); upd();
    }

    const consent = document.getElementById('consent');
    const email = document.getElementById('email');
    consent?.addEventListener('change', () => {
      email.disabled = !consent.checked;
      if (consent.checked) email.focus();
      if (!consent.checked) email.value = '';
    });

    const inputImages = document.getElementById('images');
    const preview = document.getElementById('preview');
    inputImages?.addEventListener('change', () => {
      preview.innerHTML = '';
      const files = [...inputImages.files].slice(0, 20);
      files.forEach(file => {
        const url = URL.createObjectURL(file);
        const fig = document.createElement('figure');
        fig.className = 'preview-item';
        fig.innerHTML = `
          <img src="${url}" alt="Imagem selecionada" />
          <figcaption class="muted">${file.name}</figcaption>
        `;
        preview.appendChild(fig);
      });
    });

    const form = document.getElementById('suggestForm');
      form?.addEventListener('submit', (e) => {
        const title = document.getElementById('gameTitle');
        const genre = document.getElementById('genre');
        const platforms = [...document.querySelectorAll('input[name="platforms[]"]:checked')];

        let ok = true;
        if (!title.value.trim()) { ok = false; title.focus(); }
        else if (!platforms.length && !toggleOther?.checked) { ok = false; alert('Selecione ao menos uma plataforma.'); }
        else if (genre.selectedIndex === 0) { ok = false; alert('Selecione um gênero.'); }

        if (!ok) e.preventDefault();      // só bloqueia se tiver erro
        // se estiver ok, NÃO chama preventDefault → o POST acontece
      });
  </script>
</body>
</html>
