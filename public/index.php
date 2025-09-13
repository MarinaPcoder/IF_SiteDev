<?php 
  session_start();
  
  
  require_once '../vendor/autoload.php';
  $titulo = "Home - Avaliações de Jogos";
  require_once './assets/components/head.php';
  require_once './assets/components/header.php';

  if (isset($_SESSION['Mensagem_redirecionamento'])) {
      echo "<script>console.log('PHP Debug: " . addslashes($_SESSION['Mensagem_redirecionamento']) . "');</script>";
      unset($_SESSION['Mensagem_redirecionamento']);
  }
?>

<head>
  <link rel="stylesheet" href="./assets/css/index-styles.css">
</head>

<body>
  <div class="main-content">
    <!-- Destaque Principal -->
    <section class="highlight">
      <div class="highlight-banner">
        <img src="path/to/zelda-art.jpg" alt="The Legend of Zelda: Tears of the Kingdom" class="game-banner">
        <div class="highlight-info">
          <h1 class="game-title">The Legend of Zelda: Tears of the Kingdom</h1>
          <p class="game-release">2023</p>
          <p class="game-genres">Ação, Aventura</p>
          <p class="game-rating">Avaliação Média: 9.5/10</p>
          <p class="game-description">Embarque em uma jornada épica por Hyrule com Link e explore novas terras em busca de mistérios e desafios para salvar o reino.</p>
          <div class="highlight-buttons">
            <a href="detalhes.php" class="btn btn-gray">Ver Detalhes</a>
            <a href="avaliar.php" class="btn btn-yellow">Avaliar Agora</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Carrossel de Destaques -->
    <section class="carousel-section">
      <h2>Jogos em Destaque</h2>
      <div class="carousel">
        <button class="carousel-btn prev-btn">&#8592;</button>
        <div class="carousel-container" id="carouselContainer">
          <div class="carousel-item">
            <img src="path/to/game1.jpg" alt="Jogo 1" class="carousel-image">
            <p class="carousel-title">Jogo 1</p>
          </div>
          <div class="carousel-item">
            <img src="path/to/game2.jpg" alt="Jogo 2" class="carousel-image">
            <p class="carousel-title">Jogo 2</p>
          </div>
          <!-- Adicionar mais jogos aqui -->
        </div>
        <button class="carousel-btn next-btn">&#8594;</button>
      </div>
    </section>

    <!-- Recém-Adicionados -->
    <section class="new-releases">
      <h2>Recém-Adicionados</h2>
      <div class="new-releases-grid" id="newReleasesGrid">
        <div class="game-card">
          <img src="path/to/new-game1.jpg" alt="Novo Jogo 1" class="game-card-img">
          <p class="game-card-title">Novo Jogo 1</p>
          <p class="game-card-release">2023</p>
        </div>
        <div class="game-card">
          <img src="path/to/new-game2.jpg" alt="Novo Jogo 2" class="game-card-img">
          <p class="game-card-title">Novo Jogo 2</p>
          <p class="game-card-release">2023</p>
        </div>
        <!-- Adicionar mais jogos recém-adicionados -->
      </div>
    </section>

    <!-- Paginação -->
    <section class="pagination">
      <button class="pagination-btn">1</button>
      <button class="pagination-btn">2</button>
      <button class="pagination-btn">3</button>
      <!-- Adicionar botões de página conforme necessário -->
    </section>
  </div>

  <script src="./assets/js/index-script.js"></script>
</body>
</html>
