<?php 
    require_once '../../vendor/autoload.php';

    $titulo = 'Registro';
    require_once '../../public/assets/components/head.php';
    
    session_start();

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ../../public/index.php');
        exit;
    }
?>

</head>

<?php 
    use App\Controllers\UsuarioController;
?>

<body>
    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <input type="text" name="titulo" id="titulo" placeholder="Título do jogo">
        <label for="plataforma">Qual a plataforma do jogo: </label>
        <select name="plataforma" id="plataforma">
            <option value="pc">PC</option>
            <option value="ps5">Playstation 5</option>
            <option value="ps4">Playstation 4</option>
            <option value="one">Xbox One</option>
            <option value="xboxS">Xbox Series S</option>
            <option value="xboxX">Xbox Series X</option>
            <option value="switch">Switch</option>
        </select>
        <label for="">Data de lançamento: </label>
        <input type="date" name="data_lancamento" id="data_lancamento">

        <input type="text" name="desenvolvedora" id="desenvolvendora" placeholder="Desenvolvedora"> 
        <input type="url" name="compra" id="compra" placeholder="Link de compra">
        
        <textarea name="descricao" id="descricao">

        </textarea>
    </form>
</body>
</html>