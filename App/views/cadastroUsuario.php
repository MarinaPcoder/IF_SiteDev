<?php 
    $titulo = 'Registro';
    require_once '../../public/assets/components/head.php';
?>

</head>

<?php 
    use App\Controllers\UsuarioController;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = new UsuarioController;
        $usuario ->cadastrar(
            nome: $_POST['nomeusuario'], 
            email: $_POST['email'], 
            datadenascimento: $_POST['nascimento'], 
            senha: $_POST['senha'], 
            senha2: $_POST['senha2'], 
            bio: $_POST['bio']);
    }

    session_start(); 
    $errors = $_SESSION['msg_erro'] ?? [];
    $old    = $_SESSION['old_value'] ?? [];
    unset($_SESSION['msg_erro'], $_SESSION['old_value']);
?>

<body>
    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <input type="text" name="nomeusuario" id="nomeusuario" placeholder="Nome de usuário" value="<?=$old['nomeusuario'] ?? null?>">
        <input type="email" name="email" id="email" placeholder="Email" value="<?=$old['email']?? null?>">
        <input type="date" name="nascimento" id="nascimento" placeholder="Data de nascimento" value="<?=$old['nascimento'] ?? null?>">
        <input type="password" name="senha" id="senha" placeholder="senha" value="<?=$old['senha'] ?? null?>">
        <input type="password" name="senha2" id="senha" placeholder="Confirme a senha" value="<?=$old['senha2'] ?? null?>">
        <textarea name="bio" id="bio"  value="<?=$old['bio'] ?? null?>"></textarea>
        <input type="submit" value="Registrar">
    </form>
</body>
</html>