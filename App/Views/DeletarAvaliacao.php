<?php
    session_start();
    
    require_once '../../vendor/autoload.php';
    use App\Controllers\UsuarioController;
    use App\Controllers\AvaliacaoController;
    $usuario = new UsuarioController;
    $avaliacao = new AvaliacaoController;

    CONST CAMINHO_INDEX = './../../public/index.php';

    if (isset($_SESSION['Mensagem_redirecionamento'])) {
        echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
        unset($_SESSION['Mensagem_redirecionamento']);
    }

    if (empty($_SESSION['Usuario'])) {
        header(header: 'Location: ./loginUsuario.php');
        exit;
    } else {
        [$logado, $tipo_usuario] = $usuario->ConfereLogin(id: $_SESSION['Usuario']['Id']);

        if (!$logado) {
            header(header: 'Location: ./logout.php');
            exit;
        }
    }
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verifica se veio o id da avaliação
        $id_avaliacao = $_GET['id'] ?? null;

        if ($id_avaliacao === null || !ctype_digit((string) $id_avaliacao)) {
            $_SESSION['Mensagem_redirecionamento'] = "ID da avaliação inválido.";
            header('Location: ' . CAMINHO_INDEX);
            exit;
        }

        $id_avaliacao = (int) $id_avaliacao;

        $dadoAvaliacao = $avaliacao->Ler(id: $id_avaliacao);

        if (empty($dadoAvaliacao)) {
            $_SESSION['Mensagem_redirecionamento'] = "Avaliação não encontrada.";
            header(header: "Location: " . CAMINHO_INDEX);
            exit;
        } else {
            $_SESSION['avaliacao']['alterar']['id'] = $id_avaliacao;  
        }

    // Verifica se o usuário tem permissão para alterar a avaliação
        if ($dadoAvaliacao[0]['id_usuario'] !== $_SESSION['Usuario']['Id'] && $tipo_usuario !== 'admin') {
            $_SESSION['Mensagem_redirecionamento'] = "Você não tem permissão para alterar esta avaliação.";
            header(header: "Location: " . CAMINHO_INDEX);
            exit;
        }

    $avaliacao->deletar($id);

    header('Location: /avaliacoes');
    exit;
}