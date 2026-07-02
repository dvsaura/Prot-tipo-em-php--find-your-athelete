-- =============================================================================
-- BANCO DE DADOS: Find Your Athlete (FYA)
-- Versão: 1.0
-- Descrição: Sistema de conexão entre atletas de base e avaliadores/clubes.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS findmyathlete_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE findmyathlete_db;

-- -----------------------------------------------------------------------------
-- TABELA: usuarios
-- Armazena os dados básicos de acesso de todos os usuários do sistema.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo_conta ENUM('atleta', 'avaliador') NOT NULL,
    tema_preferido ENUM('light', 'dark') DEFAULT 'light',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo'
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: atletas_perfil
-- Extensão da tabela de usuários para quem é do tipo 'atleta'.
-- Contém dados biométricos, técnicos e links de portfólio.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS atletas_perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    posicao VARCHAR(50),
    idade INT,
    peso DECIMAL(5,2),
    altura DECIMAL(3,2),
    pe_dominante ENUM('direito', 'esquerdo', 'ambos'),
    bio TEXT,
    youtube_link VARCHAR(255),
    tiktok_link VARCHAR(255),
    instagram_link VARCHAR(255),
    curriculo_link VARCHAR(255),
    foto_perfil VARCHAR(255),
    nota_media DECIMAL(3,1) DEFAULT 0.0,
    -- Atributos Técnicos (0 a 100)
    velocidade INT DEFAULT 0,
    tecnica INT DEFAULT 0,
    fisico INT DEFAULT 0,
    visao_jogo INT DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: publicacoes
-- Publicações com imagem criadas pelos atletas em seu perfil.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS publicacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(150),
    descricao TEXT,
    imagem VARCHAR(255),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: oportunidades
-- Vagas de peneiras ou oportunidades publicadas por avaliadores/clubes.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS oportunidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_avaliador INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    categoria VARCHAR(100),
    requisitos TEXT,
    idade_min INT,
    idade_max INT,
    peso_min DECIMAL(5,2),
    pe_dominante_pref ENUM('direito', 'esquerdo', 'ambos'),
    data_limite DATE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario_avaliador) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: candidaturas
-- Vincula atletas às oportunidades nas quais se inscreveram.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_oportunidade INT NOT NULL,
    id_usuario_atleta INT NOT NULL,
    status ENUM('pendente', 'aceito', 'recusado') DEFAULT 'pendente',
    data_candidatura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_oportunidade) REFERENCES oportunidades(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_atleta) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: mensagens
-- Armazena as conversas do chat assíncrono.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_remetente INT NOT NULL,
    id_destinatario INT NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lida BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_remetente) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destinatario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: notificacoes
-- Alertas do sistema para o usuário.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensagem TEXT NOT NULL,
    categoria ENUM('mensagem', 'perfil', 'candidatura', 'sistema') DEFAULT 'sistema',
    lida BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
