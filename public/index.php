<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo de Jogos</title>
  <link rel="stylesheet" href="./assets/css/index-styles.css">
</head>
<?php 
      require_once '../vendor/autoload.php';
?>
<body>
  <div class="sidebar">
    <h2>Gêneros</h2>
    <ul id="genres">
      <li data-genre="Todos">Todos</li>
      <li data-genre="Tiro">Tiro</li>
      <li data-genre="Corrida">Corrida</li>
      <li data-genre="Esporte">Esporte</li>
      <li data-genre="Aventura">Aventura</li>
      <li data-genre="RPG">RPG</li>
    </ul>

    <h2>Plataformas</h2>
    <ul id="platforms">
      <li data-platform="Todos">Todos</li>
      <li data-platform="PC">PC</li>
      <li data-platform="PS4">PS4</li>
      <li data-platform="Xbox">Xbox One</li>
      <li data-platform="Switch">Switch</li>
    </ul>
  </div>

  <div class="main">
    <div class="top-bar">
      <h1>Catálogo de Jogos</h1>
      <input type="text" id="searchInput" class="search-bar" placeholder="Pesquisar por nome...">
    </div>

    <div class="games-grid" id="gamesGrid">
      <!-- Os jogos serão renderizados aqui via JS -->
    </div>
  </div>

  <script src="./assets/js/index-script.js"></script>
</body>
</html>
