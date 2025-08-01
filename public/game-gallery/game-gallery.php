<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Trailer Modal</title>
    <link rel="stylesheet" href="../assets/css/gallery-styles.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">Game Gallery</div>
        </div>
    </header>

    <main>
        <section class="game-catalog">
            <div class="game-card" onclick="showGameDetails()">
                <img src="game1.jpg" alt="Game 1">
                <p>Game Title</p>
            </div>
            <div class="game-card" onclick="showGameDetails()">
                <img src="game2.jpg" alt="Game 2">
                <p>Another Game</p>
            </div>
            <!-- Adicione mais cards conforme necessário -->
        </section>

        <!-- Modal -->
        <div class="modal" id="gameModal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeGameDetails()">&times;</span>
                <h2>Game Title</h2>
                <div class="modal-body">
                    <video controls class="game-trailer">
                        <source src="trailer.mp4" type="video/mp4">
                        Seu navegador não suporta vídeos.
                    </video>
                    <p class="game-rating">Pontuação: 8.7/10</p>
                    <p class="game-developer">Desenvolvedor: Game Studio</p>
                    <h3>Recomendações:</h3>
                    <div class="recommended-games">
                        <div class="recommended-item">
                            <img src="recommendation1.jpg" alt="Recommendation 1">
                            <p>Game A</p>
                        </div>
                        <div class="recommended-item">
                            <img src="recommendation2.jpg" alt="Recommendation 2">
                            <p>Game B</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/index-script.js"></script>
</body>
</html>
