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

    return ((int)($presence[(int)$userId]['last_seen'] ?? 0)) >= (time() - 180);
}

if (!empty($_SESSION['user_id'])) {
    fya_touch_presence();
}
?>