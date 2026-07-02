<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: controllers/post_controller.php
 * Descrição: Controla a criação e exclusão de publicações de atletas.
 */

require_once '../config/conexao.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$redirect = $_SERVER['HTTP_REFERER'] ?? '../views/perfil_atleta.php';

function redirectWithMessage($url, $message = '') {
    $separator = strpos($url, '?') === false ? '?' : '&';
    header('Location: ' . $url . ($message ? $separator . 'msg=' . urlencode($message) : ''));
    exit();
}

try {
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = trim($_POST['titulo_publicacao'] ?? '');
        $descricao = trim($_POST['descricao_publicacao'] ?? '');
        $imagem = null;

        if (!empty($_FILES['imagem_publicacao']) && $_FILES['imagem_publicacao']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['imagem_publicacao']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowedExt)) {
                $fileName = 'publicacao_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['imagem_publicacao']['tmp_name'], $uploadDir . $fileName)) {
                    $imagem = $fileName;
                }
            }
        }

        if ($titulo === '' && $descricao === '' && $imagem === null) {
            redirectWithMessage($redirect, 'Preencha o título, descrição ou envie uma imagem antes de publicar.');
        }

        $stmt = $pdo->prepare('INSERT INTO publicacoes (id_usuario, titulo, descricao, imagem) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $titulo, $descricao, $imagem]);

        redirectWithMessage($redirect, 'Publicação criada com sucesso!');
    }

    if ($action === 'delete') {
        $postId = intval($_GET['id'] ?? 0);

        if ($postId <= 0) {
            redirectWithMessage($redirect, 'Publicação inválida.');
        }

        $stmt = $pdo->prepare('SELECT imagem FROM publicacoes WHERE id = ? AND id_usuario = ?');
        $stmt->execute([$postId, $userId]);
        $post = $stmt->fetch();

        if (!$post) {
            redirectWithMessage($redirect, 'Publicação não encontrada ou não pertence a você.');
        }

        if (!empty($post['imagem'])) {
            $filePath = '../uploads/' . basename($post['imagem']);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $stmtDelete = $pdo->prepare('DELETE FROM publicacoes WHERE id = ? AND id_usuario = ?');
        $stmtDelete->execute([$postId, $userId]);

        redirectWithMessage($redirect, 'Publicação excluída com sucesso.');
    }
} catch (PDOException $e) {
    redirectWithMessage($redirect, 'Erro ao processar a publicação: ' . $e->getMessage());
}

redirectWithMessage($redirect);
