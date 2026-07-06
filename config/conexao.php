<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: config/conexao.php
 * Descrição: Gerencia a conexão com o banco de dados MySQL utilizando PDO.
 */

require_once __DIR__ . '/upload_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Banco de Dados
$host = 'tini.click';
$db   = 'findmyathlete_db';
$user = 'findmyathlete_db';
$pass = '54c98b46e8178f4475de2c4334a72b5!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        used_at DATETIME NULL,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS bloqueios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_bloqueado INT NOT NULL,
        data_bloqueio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (id_bloqueado) REFERENCES usuarios(id) ON DELETE CASCADE,
        UNIQUE KEY uniq_bloqueio (id_usuario, id_bloqueado)
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS follows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_seguidor INT NOT NULL,
        id_seguido INT NOT NULL,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_seguidor) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (id_seguido) REFERENCES usuarios(id) ON DELETE CASCADE,
        UNIQUE KEY uniq_follow (id_seguidor, id_seguido)
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS comentarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_publicacao INT NOT NULL,
        id_usuario INT NOT NULL,
        comentario TEXT NOT NULL,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_publicacao) REFERENCES publicacoes(id) ON DELETE CASCADE,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
} catch (\PDOException $e) {
    die("Erro crítico na conexão com o banco de dados: " . $e->getMessage());
}

function fya_touch_presence() {
    if (empty($_SESSION['user_id'])) {
        return;
    }

    $presenceFile = dirname(__DIR__) . '/uploads/.presence.json';
    $presence = [];

    if (is_file($presenceFile)) {
        $raw = @file_get_contents($presenceFile);
        if ($raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $presence = $decoded;
            }
        }
    }

    $presence[(int)$_SESSION['user_id']] = [
        'id' => (int)$_SESSION['user_id'],
        'name' => $_SESSION['user_nome'] ?? 'Usuário',
        'last_seen' => time()
    ];

    @file_put_contents($presenceFile, json_encode($presence, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function fya_is_user_online($userId) {
    $presenceFile = dirname(__DIR__) . '/uploads/.presence.json';
    if (!is_file($presenceFile)) {
        return false;
    }

    $raw = @file_get_contents($presenceFile);
    if ($raw === false) {
        return false;
    }

    $presence = json_decode($raw, true);
    if (!is_array($presence) || !isset($presence[(int)$userId])) {
        return false;
    }

    $lastSeen = (int)($presence[(int)$userId]['last_seen'] ?? 0);
    return $lastSeen >= (time() - 180);
}

function fya_cleanup_presence() {
    $presenceFile = dirname(__DIR__) . '/uploads/.presence.json';
    if (!is_file($presenceFile)) {
        return;
    }

    $raw = @file_get_contents($presenceFile);
    if ($raw === false) {
        return;
    }

    $presence = json_decode($raw, true);
    if (!is_array($presence)) {
        return;
    }

    $now = time();
    foreach ($presence as $id => $entry) {
        if (((int)($entry['last_seen'] ?? 0)) < ($now - 180)) {
            unset($presence[$id]);
        }
    }

    @file_put_contents($presenceFile, json_encode($presence, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

if (!empty($_SESSION['user_id'])) {
    fya_cleanup_presence();
    fya_touch_presence();
}
?>