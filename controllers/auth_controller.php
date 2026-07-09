<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: controllers/auth_controller.php
 * Descrição: Controla a autenticação de usuários (Login e Cadastro).
 */

require_once '../config/conexao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            header("Location: ../views/login.php?erro=" . urlencode("E-mail ou senha incorretos."));
            exit();
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
                $uploadDir = fya_upload_dir();
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

// --- PROCESSAMENTO DE RECUPERAÇÃO DE SENHA ---
if ($action === 'forgot_password') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            header('Location: ../views/login.php?msg=' . urlencode('Informe seu e-mail para recuperar a senha.'));
            exit();
        }

        try {
            $stmt = $pdo->prepare('SELECT id, nome FROM usuarios WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmtReset = $pdo->prepare('INSERT INTO password_resets (id_usuario, token, expires_at) VALUES (?, ?, ?)');
                $stmtReset->execute([$user['id'], $token, $expiresAt]);
                $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/views/resetar_senha.php?token=' . urlencode($token);
                @mail($email, 'FYA - Recuperação de senha', "Olá, use este link para redefinir sua senha: {$resetLink}");
            }

            header('Location: ../views/login.php?msg=' . urlencode('Se este e-mail estiver cadastrado, enviaremos um link de recuperação.'));
            exit();
        } catch (PDOException $e) {
            header('Location: ../views/login.php?msg=' . urlencode('Erro ao processar recuperação de senha.'));
            exit();
        }
    }
}

if ($action === 'reset_password') {
    $token = trim($_GET['token'] ?? '');
    if ($token === '') {
        die('Token inválido.');
    }

    try {
        $stmt = $pdo->prepare('SELECT id_usuario FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW()');
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            die('Link inválido ou expirado.');
        }

        $newPassword = $_POST['senha'] ?? '';
        if ($newPassword === '') {
            die('Informe a nova senha.');
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmtUser = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
        $stmtUser->execute([$hash, $reset['id_usuario']]);

        $stmtUpdate = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE token = ?');
        $stmtUpdate->execute([$token]);

        header('Location: ../views/login.php?msg=' . urlencode('Senha redefinida com sucesso. Entre com sua nova senha.'));
        exit();
    } catch (PDOException $e) {
        die('Erro ao redefinir senha: ' . $e->getMessage());
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
            }  else {
                header("Location: ../views/login.php?erro=" . urlencode("E-mail ou senha incorretos."));
                exit();
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
