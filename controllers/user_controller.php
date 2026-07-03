<?php
require_once '../config/conexao.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($nome === '' || $email === '') {
        header('Location: ../views/configuracoes.php?msg=' . urlencode('Nome e e-mail são obrigatórios.'));
        exit();
    }

    try {
        $stmtCheck = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
        $stmtCheck->execute([$email, $userId]);
        if ($stmtCheck->fetch()) {
            header('Location: ../views/configuracoes.php?msg=' . urlencode('Este e-mail já está em uso por outro usuário.'));
            exit();
        }

        $fields = ['nome = ?', 'email = ?'];
        $params = [$nome, $email];

        if ($senha !== '') {
            $fields[] = 'senha = ?';
            $params[] = password_hash($senha, PASSWORD_BCRYPT);
        }

        $params[] = $userId;
        $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['user_nome'] = $nome;
        $_SESSION['user_email'] = $email;

        header('Location: ../views/configuracoes.php?msg=' . urlencode('Dados atualizados com sucesso.'));
        exit();
    } catch (PDOException $e) {
        header('Location: ../views/configuracoes.php?msg=' . urlencode('Erro ao atualizar os dados: ' . $e->getMessage()));
        exit();
    }
}

header('Location: ../views/configuracoes.php');
exit();
