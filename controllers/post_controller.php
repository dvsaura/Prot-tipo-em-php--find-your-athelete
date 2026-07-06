<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: controllers/post_controller.php
 * Descrição: Controla a criação, edição, exclusão e engajamento de publicações.
 */

require_once '../config/conexao.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
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
            $uploadDir = fya_upload_dir();
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

    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $postId = (int)($_POST['post_id'] ?? 0);
        $titulo = trim($_POST['titulo_publicacao'] ?? '');
        $descricao = trim($_POST['descricao_publicacao'] ?? '');
        $novaImagem = null;

        if ($postId <= 0) {
            redirectWithMessage($redirect, 'Publicação inválida.');
        }

        $stmtPost = $pdo->prepare('SELECT imagem, titulo, descricao FROM publicacoes WHERE id = ? AND id_usuario = ?');
        $stmtPost->execute([$postId, $userId]);
        $postAtual = $stmtPost->fetch();

        if (!$postAtual) {
            redirectWithMessage($redirect, 'Publicação não encontrada ou não pertence a você.');
        }

        if (!empty($_FILES['imagem_publicacao']) && $_FILES['imagem_publicacao']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = fya_upload_dir();
            $ext = strtolower(pathinfo($_FILES['imagem_publicacao']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowedExt)) {
                $fileName = 'publicacao_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['imagem_publicacao']['tmp_name'], $uploadDir . $fileName)) {
                    $novaImagem = $fileName;
                    if (!empty($postAtual['imagem'])) {
                        $oldPath = fya_upload_path($postAtual['imagem']);
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                }
            }
        }

        $updates = [];
        $params = [];

        if ($titulo !== '') {
            $updates[] = 'titulo = ?';
            $params[] = $titulo;
        }
        if ($descricao !== '') {
            $updates[] = 'descricao = ?';
            $params[] = $descricao;
        }
        if ($novaImagem !== null) {
            $updates[] = 'imagem = ?';
            $params[] = $novaImagem;
        }

        if (!empty($updates)) {
            $params[] = $postId;
            $params[] = $userId;
            $stmtUpdate = $pdo->prepare('UPDATE publicacoes SET ' . implode(', ', $updates) . ' WHERE id = ? AND id_usuario = ?');
            $stmtUpdate->execute($params);
        }

        redirectWithMessage($redirect, 'Publicação atualizada com sucesso.');
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
            $filePath = fya_upload_path($post['imagem']);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $stmtDelete = $pdo->prepare('DELETE FROM publicacoes WHERE id = ? AND id_usuario = ?');
        $stmtDelete->execute([$postId, $userId]);

        redirectWithMessage($redirect, 'Publicação excluída com sucesso.');
    }

    if ($action === 'comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $postId = intval($_POST['post_id'] ?? 0);
        $commentText = trim($_POST['comentario'] ?? '');

        if ($postId <= 0 || $commentText === '') {
            redirectWithMessage($redirect, 'Comentário inválido.');
        }

        $stmt = $pdo->prepare('INSERT INTO comentarios (id_publicacao, id_usuario, comentario) VALUES (?, ?, ?)');
        $stmt->execute([$postId, $userId, $commentText]);
        redirectWithMessage($redirect, 'Comentário publicado.');
    }

    if ($action === 'like') {
        $postId = intval($_GET['id'] ?? 0);
        if ($postId <= 0) {
            redirectWithMessage($redirect, 'Publicação inválida.');
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_publicacao INT NOT NULL,
            id_usuario INT NOT NULL,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_publicacao) REFERENCES publicacoes(id) ON DELETE CASCADE,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;");

        $stmt = $pdo->prepare('SELECT id FROM likes WHERE id_publicacao = ? AND id_usuario = ?');
        $stmt->execute([$postId, $userId]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmtDel = $pdo->prepare('DELETE FROM likes WHERE id = ?');
            $stmtDel->execute([$exists['id']]);
            redirectWithMessage($redirect, 'Você não curte mais esta publicação.');
        } else {
            $stmtIns = $pdo->prepare('INSERT INTO likes (id_publicacao, id_usuario) VALUES (?, ?)');
            $stmtIns->execute([$postId, $userId]);
            redirectWithMessage($redirect, 'Publicação curtida.');
        }
    }
} catch (PDOException $e) {
    redirectWithMessage($redirect, 'Erro ao processar a publicação: ' . $e->getMessage());
}

if ($action === 'follow') {
    $targetId = intval($_GET['user_id'] ?? 0);
    if ($targetId > 0 && $targetId !== $userId) {
        $stmt = $pdo->prepare('SELECT id FROM follows WHERE id_seguidor = ? AND id_seguido = ?');
        $stmt->execute([$userId, $targetId]);
        if ($stmt->fetch()) {
            $stmtDelete = $pdo->prepare('DELETE FROM follows WHERE id_seguidor = ? AND id_seguido = ?');
            $stmtDelete->execute([$userId, $targetId]);
            redirectWithMessage($redirect, 'Você deixou de seguir este usuário.');
        } else {
            $stmtInsert = $pdo->prepare('INSERT INTO follows (id_seguidor, id_seguido) VALUES (?, ?)');
            $stmtInsert->execute([$userId, $targetId]);
            redirectWithMessage($redirect, 'Você passou a seguir este usuário.');
        }
    }
}

redirectWithMessage($redirect);
