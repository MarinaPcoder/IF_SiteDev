
<?php
    require_once '../../vendor/autoload.php';

    use App\Controllers\UsuarioController;
    use App\Controllers\JogoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;

    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    } else {
        [$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);
    
        if (!$logado || $tipo_usuario !== 'admin') {

        $_SESSION['Mensagem_redirecionamento'] = "Usuario não existe ou não tem permissão. Redirecionado para ./logout.php";

            header(header: 'Location: ./logout.php');
            exit;
        }
    }

    function PaginaInicial(): never {
        header(header: 'Location: ../../public/index.php');
        exit;
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === null || $id === false) PaginaInicial();

    if (!$jogo->ExisteJogo(idJogo: $id)) {
        header(header: 'Location: ./../../public');
        exit;
    }

    if ($_GET['deletar_imagem'] ?? False) {
        try {
            if (!isset($_GET['id_imagem'], $_GET['caminho'])) {
                $_SESSION['Mensagem_redirecionamento'] = "Nenhuma imagem selecionada.";
                header(header: 'Location: ./upload_form.php');
                exit;
            }
    
            $id_imagem = (int) $_GET['id_imagem'];
    
            if (!is_int($id_imagem) && $id_imagem >= 0) {
                $_SESSION['Mensagem_redirecionamento'] = "ID de imagem inválido.";
                header(header: 'Location: ./upload_form.php');
                exit;
            }

            $caminho_imagem = $_GET['caminho'];

            $ordem = $_GET['ordem'];

            $resultado = $jogo->DeletarImagem(id: $id_imagem, caminho: $caminho_imagem, ordem: $ordem, id_jogo: $id);

            if ($resultado) {
                $_SESSION['Mensagem_redirecionamento'] = "Imagem deletada com sucesso.";
            }

        } catch (\Throwable $th) {
            $_SESSION['Mensagem_redirecionamento'] = "Erro ao deletar imagem: " . $th->getMessage();
        }

        header(header: "Location: ./upload_form.php?id={$id}");
        exit;

    } else {
        $_SESSION['Mensagem_redirecionamento'] = "Nenhuma imagem selecionada.";
        header(header: "Location: ./upload_form.php?id={$id}");
        exit;
    }