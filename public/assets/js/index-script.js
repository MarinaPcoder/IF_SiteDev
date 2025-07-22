 const jogos = [
      {
        nome: "Dirt Rally",
        desenvolvedora: "Codemasters",
        genero: "Corrida",
        plataforma: "PC",
        imagem: "https://image.api.playstation.com/vulcan/img/rnd/202010/2913/v5YvYxw2vBxiWcIl94Uw0Dov.png"
      },
      {
        nome: "Battlefield 5",
        desenvolvedora: "EA DICE",
        genero: "Tiro",
        plataforma: "PS4",
        imagem: "https://cdn1.epicgames.com/salesEvent/salesEvent/EGS_BattlefieldV_DICE_Editions_S2_1200x1600-e9f1ab0a2d94bc49fa748a4dbec4ad9e"
      },
      {
        nome: "Need For Speed Heat",
        desenvolvedora: "Electronic Arts",
        genero: "Corrida",
        plataforma: "PS4",
        imagem: "https://image.api.playstation.com/vulcan/img/cfn/11307/2D1e85B1QbPvzCuFCKuJb4LO7xFXv9KQn4WBih8rk5YvXIrjcC0Skm6Oce5v1dFk.png"
      },
      {
        nome: "Call of Duty: MW",
        desenvolvedora: "Activision",
        genero: "Tiro",
        plataforma: "Xbox",
        imagem: "https://image.api.playstation.com/vulcan/img/cfn/11307/r9Erp3WPG6YwqAlnkfrMjLoWZKJcYAKeAbMEjOb-X2VvBFfUrJbbAN4NsV_gGQxt.png"
      },
      {
        nome: "FIFA 20",
        desenvolvedora: "EA Sports",
        genero: "Esporte",
        plataforma: "PS4",
        imagem: "https://image.api.playstation.com/vulcan/ap/rnd/202206/1714/UXkXYxeoydEXu2gWsTjvvvnx.png"
      }
    ];

    const gamesGrid = document.getElementById("gamesGrid");
    const searchInput = document.getElementById("searchInput");

    function renderGames(lista) {
      gamesGrid.innerHTML = "";
      lista.forEach(jogo => {
        gamesGrid.innerHTML += `
          <div class="game-card">
            <img src="${jogo.imagem}" alt="${jogo.nome}">
            <h4>${jogo.nome}</h4>
            <p>${jogo.desenvolvedora}</p>
          </div>
        `;
      });
    }

    renderGames(jogos);

    searchInput.addEventListener("input", () => {
      const texto = searchInput.value.toLowerCase();
      const filtrados = jogos.filter(j => j.nome.toLowerCase().includes(texto));
      renderGames(filtrados);
    });

    document.querySelectorAll("#genres li").forEach(item => {
      item.addEventListener("click", () => {
        const genero = item.getAttribute("data-genre");
        const filtrados = genero === "Todos" ? jogos : jogos.filter(j => j.genero === genero);
        renderGames(filtrados);
      });
    });

    document.querySelectorAll("#platforms li").forEach(item => {
      item.addEventListener("click", () => {
        const plataforma = item.getAttribute("data-platform");
        const filtrados = plataforma === "Todos" ? jogos : jogos.filter(j => j.plataforma === plataforma);
        renderGames(filtrados);
      });
    });