<?php 

    require_once '../../vendor/autoload.php';

    $titulo = 'Exclusão de conta';
    require_once '../../public/assets/components/head.php';
    
    session_start();

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    } 

    use App\Controllers\UsuarioController;
    
?>

</head>
<?php 
    $usuario = new UsuarioController;
    $dado = ($usuario -> getUsuario(id: $_SESSION['Usuario']['Id']))[0];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
         try {
            list($senha, $ErrosSenha) = $usuario ->VerificarSenha($_POST['senha'], $_POST['senha']);
            
            $erros = $ErrosSenha;

            $usuario->ExcluirUsuario($dado['id_usuario'], $senha);

         } catch(\Exception){
            $erros[match ($e -> getCode()) {
                        43 => 'Senha',  
                        default => 'Indefinido',
                    }]
                    
                    [] = $e -> getMessage();
         } catch (\PDOException $th) {
            error_log(message: "Erro PDO: " . $e->getMessage());

            $_SESSION['msg_erro']['Indefinido'][] = "Erro ao deletar o usuário: " . $e->getMessage();
            
         } catch (Throwable $t) {
            error_log(message: "Erro inesperado: " . $t->getMessage());
            echo "Ocorreu um erro inesperado. Tente novamente mais tarde." . $t->getMessage();
        }
    }

    $erros = $_SESSION['msg_erro'] ?? [];
    
    unset($_SESSION['msg_erro']);
?>
<body>
    
    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <p>Você tem certeza que deseja excluir a conta de <?=$dado['nome_usuario']?>?</p>
        <input placeholder="Insira a sua senha:" type="password" name="senha" id="senha">
        <input type="submit"  value="Sim">
    </form>
    <button type="button" onclick="redirecionar()">Não</button>

    <script>
        function redirecionar() {
            window.location.href = "./Show.php";
        }
    </script>

</body>
</html>