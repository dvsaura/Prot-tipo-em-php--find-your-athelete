<?php
session_start();
require_once '../config/conexao.php';

$token = trim($_GET['token'] ?? '');
$alert = '';

if ($token !== '') {
    try {
        $stmt = $pdo->prepare('SELECT id_usuario FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW()');
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        if (!$reset) {
            $alert = 'Link inválido ou expirado.';
        }
    } catch (PDOException $e) {
        $alert = 'Não foi possível validar o token.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-3">Redefinir senha</h3>
                        <p class="text-muted">Crie uma nova senha para acessar sua conta.</p>
                        <?php if ($alert): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($alert); ?></div>
                        <?php endif; ?>
                        <?php if ($token !== '' && empty($alert)): ?>
                            <form action="../controllers/auth_controller.php?action=reset_password&token=<?php echo urlencode($token); ?>" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nova senha</label>
                                    <input type="password" name="senha" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-fya w-100">Salvar nova senha</button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline-secondary">Voltar ao login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
