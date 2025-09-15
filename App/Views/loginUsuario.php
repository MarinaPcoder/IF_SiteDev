<?php 
    session_start();
    require_once '../../vendor/autoload.php';

    $titulo = 'Login';
    require_once '../../public/assets/components/head.php';
 
    const CAMINHO_PUBLIC = './../../public/';
    const CAMINHO_INDEX = './../../public/index.php';


    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }

    if (isset($_SESSION['Usuario'])) {
        header(header: 'Location: ../../public/index.php');
        exit;
    }

    use App\Controllers\UsuarioController;
    $usuario = new UsuarioController;
    
?>
</head>

<?php 
    $erros = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $erros = [];
        
        if (isset($_POST['email']) and isset($_POST['senha'])) {
            
            [$email, $errosEmail]   = $usuario -> VerificarEmail(email: $_POST['email']);
            [$senha, $errosSenha]   = $usuario -> VerificarSenha(senha: $_POST['senha'], senha2: $_POST['senha']);

            $erros = array_merge($errosEmail, $errosSenha);

            if (empty($erros)) {
                try {
                    $usuario -> Login(email: $email, senha: $senha);
                } catch (\Exception $e) {
                    
                    $erros[match ($e -> getCode()) {
                        43 => 'Senha',
                        30 => 'Email',
                        default => 'Indefinido',
                    }][] = $e -> getMessage();
                }
                    
            }
            
        }
    }
?>

<body>

<link rel="stylesheet" href="../">

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

    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <input type="email" name="email" id="email">
        <input type="password" name="senha" id="senha">
        <input type="submit" value="Logar">
    </form>

<script>
// Storm • Login UI helpers (não altera o PHP existente)
(function(){
  const SIGNUP_URL = 'signup.php'; // ajuste se a sua rota for outra

  // ===== Mostrar/ocultar senha =====
  const passInput = document.querySelector('input[type="password"], #password, [name="password"]');
  if (passInput) {
    // cria o botão se não existir
    let wrap = passInput.closest('.input-wrap');
    if (!wrap) {
      // envolve sem quebrar layouts existentes
      wrap = document.createElement('div');
      wrap.className = 'input-wrap';
      passInput.parentNode.insertBefore(wrap, passInput);
      wrap.appendChild(passInput);
    }
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'toggle-pass';
    btn.setAttribute('aria-label', 'Mostrar/ocultar senha');
    btn.innerHTML = `
      <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
        <path d="M12 5c5.5 0 9.5 4.7 10.8 6.5a1.6 1.6 0 0 1 0 1.9C21.5 15.3 17.5 20 12 20S2.5 15.3 1.2 13.4a1.6 1.6 0 0 1 0-1.9C2.5 9.7 6.5 5 12 5Zm0 3.2a3.8 3.8 0 1 0 0 7.6 3.8 3.8 0 0 0 0-7.6Z"/>
      </svg>`;
    wrap.appendChild(btn);

    btn.addEventListener('click', ()=>{
      const isText = passInput.type === 'text';
      passInput.type = isText ? 'password' : 'text';
      passInput.focus();
    });
  }

  // ===== Validação leve =====
  const form = document.querySelector('form[action*="login"], form#loginForm, form[name="login"], form'); // tenta pegar o form correto
  if (form) {
    form.setAttribute('novalidate','novalidate');
    form.addEventListener('submit', (e)=>{
      // e-mail
      const email = form.querySelector('input[type="email"], #email, [name="email"]');
      const password = form.querySelector('input[type="password"], #password, [name="password"]');

      let ok = true;

      if (email) {
        const v = String(email.value || '').trim();
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
        if (!v || !isValid) { email.classList.add('is-invalid'); ok = false; }
        else email.classList.remove('is-invalid');
      }

      if (password) {
        const v = String(password.value || '').trim();
        if (!v) { password.classList.add('is-invalid'); ok = false; }
        else password.classList.remove('is-invalid');
      }

      if (!ok) {
        e.preventDefault();
        // foco no primeiro inválido
        const firstInvalid = form.querySelector('.is-invalid');
        firstInvalid?.focus();
      }
    });
  }

  // ===== Navegar para Sign Up =====
  // Qualquer botão/link com id ou data-attr:
  const signupTriggers = [
    '#btnSignUp',
    'a[href*="signup"]',
    '[data-goto="signup"]',
    '.js-signup'
  ];
  document.addEventListener('click', (ev)=>{
    const btn = ev.target.closest(signupTriggers.join(','));
    if (!btn) return;
    // se já for <a href="..."> deixa seguir; senão redireciona:
    if (!btn.getAttribute('href')) {
      ev.preventDefault();
      window.location.assign(SIGNUP_URL);
    }
  });

})();
</script>


</body>