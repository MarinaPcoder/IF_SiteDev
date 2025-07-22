// Função para exibir o modal com detalhes do jogo
function showGameDetails() {
    // Aqui você pode personalizar os detalhes do jogo com base no item clicado
    const modal = document.getElementById('gameModal');
    modal.style.display = 'flex';
}

// Função para fechar o modal
function closeGameDetails() {
    const modal = document.getElementById('gameModal');
    modal.style.display = 'none';
}

// Fechar o modal se clicar fora da área do conteúdo
window.onclick = function(event) {
    const modal = document.getElementById('gameModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
