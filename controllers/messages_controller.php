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

if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactId = intval($_POST['contact_id'] ?? 0);
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($contactId > 0 && $contactId !== $userId && $mensagem !== '') {
        try {
            $stmtContact = $pdo->prepare('SELECT id FROM usuarios WHERE id = ? AND id != ?');
            $stmtContact->execute([$contactId, $userId]);

            if ($stmtContact->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO mensagens (id_remetente, id_destinatario, mensagem, data_envio) VALUES (?, ?, ?, ?)');
                $stmt->execute([$userId, $contactId, $mensagem , date('Y-m-d H:i:s')]);
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
