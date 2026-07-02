<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: controllers/auth_controller.php
 * Descrição: Controla a autenticação de usuários (Login e Cadastro).
 */

require_once '../config/conexao.php';
session_start();

// Captura a ação via GET
$action = $_GET['action'] ?? '';

// --- PROCESSAMENTO DE LOGOUT ---
if ($action === 'logout') {
    session_destroy();
    header("Location: ../views/login.php");
    exit();
}

// --- PROCESSAMENTO DE CADASTRO ---
if ($action === 'register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tipo_conta = $_POST['tipo_conta'] ?? 'atleta';
        $senha = $_POST['senha'] ?? '';

        // Validação básica
        if (empty($nome) || empty($email) || empty($senha)) {
            die("Erro: Por favor, preencha todos os campos obrigatórios.");
        }

        try {
            // Verifica se o e-mail já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                die("Erro: Este e-mail já está cadastrado no sistema.");
            }

            // Segurança: Hashing da senha com bcrypt
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

            // Inserção no banco
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo_conta) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senhaHash, $tipo_conta]);
            $userId = $pdo->lastInsertId();

            // Upload de Foto de Perfil opcional no cadastro
            $fotoPerfilFile = null;
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($ext, $allowedExt)) {
                    $fotoPerfilFile = "perfil_{$userId}_" . time() . "." . $ext;
                    move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $uploadDir . $fotoPerfilFile);
                }
            }

            // Cria o registro de perfil do usuário
            $stmtPerfil = $pdo->prepare("INSERT INTO atletas_perfil (id_usuario, foto_perfil) VALUES (?, ?)");
            $stmtPerfil->execute([$userId, $fotoPerfilFile]);

            // Redireciona para o login com mensagem de sucesso
            header("Location: ../views/login.php?success=registered");
            exit();

        } catch (PDOException $e) {
            die("Erro no banco de dados: " . $e->getMessage());
        }
    }
}

// --- PROCESSAMENTO DE LOGIN ---
if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            die("Erro: E-mail e senha são obrigatórios.");
        }

        try {
            // Busca o usuário pelo e-mail
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verifica se usuário existe e se a senha bate com o hash
            if ($user && password_verify($senha, $user['senha'])) {
                // Busca a foto de perfil associada ao usuário
                $stmtPerfil = $pdo->prepare("SELECT foto_perfil FROM atletas_perfil WHERE id_usuario = ?");
                $stmtPerfil->execute([$user['id']]);
                $perfil = $stmtPerfil->fetch();

                if (!$perfil) {
                    $stmtCreatePerfil = $pdo->prepare("INSERT INTO atletas_perfil (id_usuario) VALUES (?)");
                    $stmtCreatePerfil->execute([$user['id']]);
                    $perfil = ['foto_perfil' => null];
                }

                // Login bem sucedido: Salva dados essenciais na sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_tipo'] = $user['tipo_conta'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_avatar'] = $perfil['foto_perfil'] ?? '';

                // Redireciona para o feed inicial
                header("Location: ../views/feed.php");
                exit();
            } else {
                die("Erro: E-mail ou senha incorretos.");
            }

        } catch (PDOException $e) {
            die("Erro no banco de dados: " . $e->getMessage());
        }
    }
}

// Se cair aqui sem ação definida, volta para o login
header("Location: ../views/login.php");
exit();
?>
