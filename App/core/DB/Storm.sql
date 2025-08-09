CREATE DATABASE  IF NOT EXISTS `Storm` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `Storm`;

DROP TABLE IF EXISTS Usuario;

CREATE TABLE Usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nome_usuario VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  data_nascimento DATE,
  tipo_perfil ENUM('admin','usuario') DEFAULT 'usuario',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  pontos_gamificacao INT DEFAULT 0,
  status_ativo TINYINT(1) DEFAULT 1,
  bio TEXT
);

DROP TABLE IF EXISTS Jogo;

CREATE TABLE Jogo (
  id_jogo INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  descricao TEXT,
  desenvolvedora VARCHAR(255),
  data_lancamento DATE,
  link_compra VARCHAR(255),
  plataforma VARCHAR(255)
);

DROP TABLE IF EXISTS Comentario;

CREATE TABLE Comentario (
  id_comentario INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_jogo INT NOT NULL,
  texto_comentario TEXT NOT NULL,
  data_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  comentario_pai_id INT NULL,
  CONSTRAINT fk_comentario_usuario
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
      ON DELETE CASCADE,
  CONSTRAINT fk_comentario_jogo
    FOREIGN KEY (id_jogo) REFERENCES Jogo(id_jogo)
      ON DELETE CASCADE,
  CONSTRAINT fk_comentario_pai
    FOREIGN KEY (comentario_pai_id) REFERENCES Comentario(id_comentario)
      ON DELETE SET NULL
);

DROP TABLE IF EXISTS Avaliacao;

CREATE TABLE Avaliacao (
  id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_jogo INT NOT NULL,
  justificativa TEXT,
  nota TINYINT UNSIGNED CHECK (nota BETWEEN 0 AND 10),
  data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_avaliacao_usuario FOREIGN KEY (id_usuario)
      REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_avaliacao_jogo FOREIGN KEY (id_jogo)
      REFERENCES Jogo(id_jogo) ON DELETE CASCADE
);

DROP TABLE IF EXISTS Genero;

CREATE TABLE Genero (
  id_genero INT AUTO_INCREMENT PRIMARY KEY,
  nome_genero VARCHAR(60) NOT NULL UNIQUE
);

DROP TABLE IF EXISTS Jogo_Genero;

-- tabela ponte M:N
CREATE TABLE Jogo_Genero (
  id_jogo INT NOT NULL,
  id_genero INT NOT NULL,
  PRIMARY KEY (id_jogo, id_genero),
  CONSTRAINT fk_jogogen_jogo FOREIGN KEY (id_jogo)
      REFERENCES Jogo(id_jogo) ON DELETE CASCADE,
  CONSTRAINT fk_jogogen_genero FOREIGN KEY (id_genero)
      REFERENCES Genero(id_genero) ON DELETE CASCADE
);

DROP TABLE IF EXISTS Jogo_Imagem;

CREATE TABLE Jogo_Imagem (
    id_imagem INT AUTO_INCREMENT PRIMARY KEY,
    id_jogo INT NOT NULL,
    tipo ENUM('logo', 'banner', 'screenshot', 'video') NOT NULL,
    caminho VARCHAR(255) NOT NULL,
    ordem_exib TINYINT UNSIGNED DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_img_jogo FOREIGN KEY (id_jogo) 
             REFERENCES Jogo(id_jogo) ON DELETE CASCADE,
    INDEX tipo_ordem (id_jogo, tipo, ordem_exib)
);