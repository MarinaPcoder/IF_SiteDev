<?php session_start(); ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nome do Site</title>
    <link rel="stylesheet" href="/public/assets/css/header-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-container">
            <!-- Logo -->
            <div class="logo">
                <img src="logo.png" alt="Logo do Site">
            </div>

            <!-- Menu e ícone de abrir sidebar -->
            <div class="menu">
                <button class="menu-btn" id="menu-toggle">☰</button>
                <nav class="sidebar" id="sidebar">
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a href="generos.php">Gêneros</a>
                            <ul class="sub-menu">
                                <?php
                                    // Conectar ao banco de dados e recuperar os gêneros
                                    // Exemplo com MySQL (substitua pelo seu código de conexão)
                                    $conn = new mysqli('localhost', 'usuario', 'senha', 'banco');
                                    $result = $conn->query("SELECT nome FROM generos");

                                    while($row = $result->fetch_assoc()) {
                                        echo "<li><a href='genero.php?nome=" . $row['nome'] . "'>" . $row['nome'] . "</a></li>";
                                    }
                                ?>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Campo de Pesquisa -->
            <div class="search-container">
                <input type="text" placeholder="Pesquisar jogos..." id="search-box">
            </div>

            <!-- Login ou Foto de Perfil -->
            <div class="user-profile">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="perfil.php">
                        <img src="<?php echo $_SESSION['usuario']['foto_perfil']; ?>" alt="Foto de Perfil" class="user-img">
                    </a>
                <?php else: ?>
                    <a href="login.php" class="login-btn">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        // JavaScript para abrir/fechar o sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
