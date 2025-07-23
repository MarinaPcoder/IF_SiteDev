<?php 
    $titulo = 'Registro';
    require_once '../../public/assets/components/head.php';
?>

</head>

<?php 
    if(isset($erro)) {
        echo "<script>";
        foreach ($erro as $i => $msg) {
            echo 
            "erro["+$i+"] = "+$msg;
        }
        echo "</script>";
    }
?>

<body>
    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <input type="text" name="nomeusuario" id="nomeusuario" placeholder="Nome de usuário" >
        <input type="email" name="email" id="email" placeholder="Email">
        <input type="date" name="nascimento" id="nascimento" placeholder="Data de nascimento">
        <input type="password" name="senha" id="senha" placeholder="senha">
        <input type="password" name="senhaconfirma" id="senha" placeholder="Confirme a senha">
        <textarea name="bio" id="bio"></textarea>
        <input type="submit" value="Registrar">
    </form>
</body>
</html>