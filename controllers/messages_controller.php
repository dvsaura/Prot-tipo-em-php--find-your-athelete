<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: controllers/messages_controller.php
 * Descrição: Controla envio e leitura de mensagens entre usuários.
 */

require_once '../config/conexao.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'block' && isset($_GET['user_id'])) {
    $targetId = intval($_GET['user_id']);
    if ($targetId > 0 && $targetId !== $userId) {
        try {
            $stmt = $pdo->prepare('INSERT INTO bloqueios (id_usuario, id_bloqueado) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_bloqueado = VALUES(id_bloqueado)');
            $stmt->execute([$userId, $targetId]);
        } catch (PDOException $e) {
        }
    }
    header('Location: ../views/mensagens.php');
    exit();
}

if ($action === 'presence') {
    fya_touch_presence();
    $presenceFile = dirname(__DIR__) . '/uploads/.presence.json';
    $onlineIds = [];

    if (is_file($presenceFile)) {
        $raw = @file_get_contents($presenceFile);
        if ($raw !== false) {
            $presence = json_decode($raw, true);
            if (is_array($presence)) {
                $now = time();
                foreach ($presence as $id => $entry) {
                    if (((int)($entry['last_seen'] ?? 0)) >= ($now - 180)) {
                        $onlineIds[] = (int)$id;
                    }
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['online_ids' => $onlineIds]);
    exit();
}

if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactId = intval($_POST['contact_id'] ?? 0);
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($contactId > 0 && $contactId !== $userId && $mensagem !== '') {
        try {
            $stmtContact = $pdo->prepare('SELECT id FROM usuarios WHERE id = ? AND id != ?');
            $stmtContact->execute([$contactId, $userId]);

            if ($stmtContact->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO mensagens (id_remetente, id_destinatario, mensagem) VALUES (?, ?, ?)');
                $stmt->execute([$userId, $contactId, $mensagem]);
            }
        } catch (PDOException $e) {
            // Erro não fatal para o envio de mensagem.
        }
    }

    header('Location: ../views/mensagens.php?contact=' . $contactId);
    exit();
}

header('Location: ../views/mensagens.php');
exit();
