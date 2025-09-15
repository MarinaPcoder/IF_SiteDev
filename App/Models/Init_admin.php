
 <?php 

    require_once '../../vendor/autoload.php';

    use App\Core\DB\Conexao;

    $pdo = Conexao::getInstancia();
    $pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);

    $nome  = 'Pedro';
    $email = 'pedrohtpgbi2007@gmail.com';
    $senha = 'Pedro!13579';
    $senhamd5  = md5(string: $senha);

try {
    $pdo->beginTransaction();

    // já existe?
    $q = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE tipo_perfil = 'admin'");
    $q->execute();
    if ($q->fetchColumn() > 0) {
        $pdo->rollBack();
        exit("Já existe admin. Nada a fazer.\n");
    }

    $sql = "
        INSERT INTO Usuario (nome_usuario, email, senha, tipo_perfil)
        VALUES (:nome, :email, :senha, 'admin')
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'  => $nome,
        ':email' => $email,
        ':senha' => $senhamd5,
    ]);

    $pdo->commit();
    echo "Admin criado com sucesso.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
}
header('Location: ../../public');
exit;