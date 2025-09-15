
<?php
    require_once '../../vendor/autoload.php';

    use App\Controllers\UsuarioController;
    use App\Controllers\JogoController;
    $usuario = new UsuarioController;
    $jogo = new JogoController;

    const CAMINHO_PUBLIC = './../../public/';
    const CAMINHO_INDEX = './../../public/index.php';

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

    $id_imagem = (int) filter_input(INPUT_GET, 'id_imagem', FILTER_VALIDATE_INT);
    
    $jogoDados = $jogo->LerImagem(idImagem: $id_imagem);

    if (!$jogoDados) {
        $_SESSION['Mensagem_redirecionamento'] = "Imagem não encontrada. Redirecionado para ./../../public";
        PaginaInicial();
    }

    if ($id_imagem === null || $id_imagem === false) PaginaInicial();

    if (!$jogo->ExisteJogo(idJogo: $jogoDados['id_jogo'])) {
        $_SESSION['Mensagem_redirecionamento'] = "Jogo não encontrado. Redirecionado para ./../../public";
        PaginaInicial();
    }

    if ($_GET['deletar_imagem'] ?? False) {
        try {
            if ($id_imagem === null || $id_imagem === false) {
                $_SESSION['Mensagem_redirecionamento'] = "Nenhuma imagem selecionada.";
                header(header: 'Location: ./upload_form.php');
                exit;
            }
    
            if ($id_imagem <= 0) {
                $_SESSION['Mensagem_redirecionamento'] = "ID de imagem inválido.";
                header(header: 'Location: ./upload_form.php');
                exit;
            }

            // Ler dados da imagem;

            $caminho_imagem = $jogoDados['caminho'];

            $ordem = $jogoDados['ordem_exib'];

            $id = $jogoDados['id_jogo'];

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