<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/editar_perfil.php
 * Descrição: Página para o atleta atualizar seus dados, atributos e subir foto de perfil.
 */
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";

// Garante registro de perfil para o usuário
$stmtInit = $pdo->prepare("SELECT id FROM atletas_perfil WHERE id_usuario = ?");
$stmtInit->execute([$userId]);
if (!$stmtInit->fetch()) {
    $stmtCreate = $pdo->prepare("INSERT INTO atletas_perfil (id_usuario) VALUES (?)");
    $stmtCreate->execute([$userId]);
}

$stmtUser = $pdo->prepare('SELECT nome, email FROM usuarios WHERE id = ?');
$stmtUser->execute([$userId]);
$userData = $stmtUser->fetch();

$stmt = $pdo->prepare("SELECT * FROM atletas_perfil WHERE id_usuario = ?");
$stmt->execute([$userId]);
$perfil = $stmt->fetch();

// --- PROCESSAMENTO DO FORMULÁRIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeExibicao = trim($_POST['nome_exibicao'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $posicao = trim($_POST['posicao'] ?? '') !== '' ? trim($_POST['posicao'] ?? '') : ($perfil['posicao'] ?? '');
    $idade = trim($_POST['idade'] ?? '') !== '' ? (int)$_POST['idade'] : ((int)($perfil['idade'] ?? 0));
    $peso = trim($_POST['peso'] ?? '') !== '' ? (float)$_POST['peso'] : ((float)($perfil['peso'] ?? 0));
    $altura = trim($_POST['altura'] ?? '') !== '' ? (float)$_POST['altura'] : ((float)($perfil['altura'] ?? 0));
    $modalidade = trim($_POST['modalidade'] ?? '') !== '' ? trim($_POST['modalidade'] ?? '') : ($perfil['modalidade'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '') !== '' ? trim($_POST['cidade'] ?? '') : ($perfil['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '') !== '' ? trim($_POST['estado'] ?? '') : ($perfil['estado'] ?? '');
    $pais = trim($_POST['pais'] ?? '') !== '' ? trim($_POST['pais'] ?? '') : ($perfil['pais'] ?? '');
    $bio = trim($_POST['bio'] ?? '') !== '' ? trim($_POST['bio'] ?? '') : ($perfil['bio'] ?? '');
    $historico = trim($_POST['historico_campeonatos'] ?? '') !== '' ? trim($_POST['historico_campeonatos'] ?? '') : ($perfil['historico_campeonatos'] ?? '');
    $youtube = trim($_POST['youtube_link'] ?? '') !== '' ? trim($_POST['youtube_link'] ?? '') : ($perfil['youtube_link'] ?? '');
    $tiktok = trim($_POST['tiktok_link'] ?? '') !== '' ? trim($_POST['tiktok_link'] ?? '') : ($perfil['tiktok_link'] ?? '');
    $instagram = trim($_POST['instagram_link'] ?? '') !== '' ? trim($_POST['instagram_link'] ?? '') : ($perfil['instagram_link'] ?? '');
    $curriculo = trim($_POST['curriculo_link'] ?? '') !== '' ? trim($_POST['curriculo_link'] ?? '') : ($perfil['curriculo_link'] ?? '');

    try {
        if ($nomeExibicao === '' || $email === '') {
            throw new Exception('Nome e e-mail são obrigatórios.');
        }

        $stmtCheckEmail = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
        $stmtCheckEmail->execute([$email, $userId]);
        if ($stmtCheckEmail->fetch()) {
            throw new Exception('Este e-mail já está em uso por outro usuário.');
        }

        $userUpdateSql = 'UPDATE usuarios SET nome = ?, email = ?';
        $userParams = [$nomeExibicao, $email];
        if ($senha !== '') {
            $userUpdateSql .= ', senha = ?';
            $userParams[] = password_hash($senha, PASSWORD_BCRYPT);
        }
        $userUpdateSql .= ' WHERE id = ?';
        $userParams[] = $userId;
        $stmtUserUpdate = $pdo->prepare($userUpdateSql);
        $stmtUserUpdate->execute($userParams);

        $_SESSION['user_nome'] = $nomeExibicao;
        $_SESSION['user_email'] = $email;

        // 1. Upload de Foto de Perfil
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = fya_upload_dir();
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowedExt)) {
                $fileName = "perfil_" . $userId . "_" . time() . "." . $ext;
                if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $uploadDir . $fileName)) {
                    $stmtFoto = $pdo->prepare("UPDATE atletas_perfil SET foto_perfil = ? WHERE id_usuario = ?");
                    $stmtFoto->execute([$fileName, $userId]);
                    $_SESSION['user_avatar'] = $fileName;
                }
            }
        }

        // 2. Atualização dos dados principais do perfil
        $stmt = $pdo->prepare(" 
            UPDATE atletas_perfil 
            SET posicao = ?, idade = ?, peso = ?, altura = ?, modalidade = ?, cidade = ?, estado = ?, pais = ?, bio = ?, historico_campeonatos = ?, 
                youtube_link = ?, tiktok_link = ?, instagram_link = ?, curriculo_link = ?
            WHERE id_usuario = ?
        ");
        $stmt->execute([$posicao, $idade, $peso, $altura, $modalidade, $cidade, $estado, $pais, $bio, $historico, $youtube, $tiktok, $instagram, $curriculo, $userId]);

        $stmt = $pdo->prepare("SELECT * FROM atletas_perfil WHERE id_usuario = ?");
        $stmt->execute([$userId]);
        $perfil = $stmt->fetch();

        $stmtUser = $pdo->prepare('SELECT nome, email FROM usuarios WHERE id = ?');
        $stmtUser->execute([$userId]);
        $userData = $stmtUser->fetch();

        $message = "<div class='alert alert-success'>Perfil e dados da conta atualizados com sucesso!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Erro ao salvar: " . $e->getMessage() . "</div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-warning'>" . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --fya-primary: #9ACD32; }
        html, body { min-height: 100%; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bs-body-bg); }
        .edit-card { background-color: var(--bs-tertiary-bg); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .btn-fya { background-color: var(--fya-primary); color: #000; font-weight: 600; border: none; }
        #main-content { padding-top: 70px; }
        .avatar-preview { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--fya-primary); margin-bottom: 1rem; }
        .attr-slider { height: 8px; border-radius: 5px; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main class="container p-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <a href="perfil_atleta.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
                        <h3 class="fw-bold m-0">Configurações do Perfil</h3>
                    </div>

                    <?php echo $message; ?>

                    <div class="edit-card">
                        <?php if (!empty($_GET['msg'])): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($_GET['msg']); ?></div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="<?php echo !empty($perfil['foto_perfil']) ? '../uploads/'.$perfil['foto_perfil'] : 'https://ui-avatars.com/api/?name='.urlencode($userData['nome'] ?? 'Usuário').'&background=9ACD32&color=fff'; ?>" class="avatar-preview" id="preview">
                                <div class="mb-3">
                                    <label class="form-label d-block">Mudar Foto de Perfil</label>
                                    <input type="file" name="foto_perfil" class="form-control w-50 mx-auto" accept="image/*" onchange="previewImage(this)">
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Posição</label>
                                    <input type="text" name="posicao" class="form-control" value="<?php echo htmlspecialchars($perfil['posicao'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Idade</label>
                                    <input type="number" name="idade" class="form-control" value="<?php echo $perfil['idade'] ?? ''; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Peso (kg)</label>
                                    <input type="number" step="0.1" name="peso" class="form-control" value="<?php echo $perfil['peso'] ?? ''; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Altura (m)</label>
                                    <input type="number" step="0.01" name="altura" class="form-control" value="<?php echo $perfil['altura'] ?? ''; ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Modalidade</label>
                                    <select name="modalidade" class="form-select">
                                        <option value="">Escolha a modalidade</option>
                                        <option value="Futebol" <?php echo ($perfil['modalidade'] ?? '') === 'Futebol' ? 'selected' : ''; ?>>Futebol</option>
                                        <option value="Basquete" <?php echo ($perfil['modalidade'] ?? '') === 'Basquete' ? 'selected' : ''; ?>>Basquete</option>
                                        <option value="Vôlei" <?php echo ($perfil['modalidade'] ?? '') === 'Vôlei' ? 'selected' : ''; ?>>Vôlei</option>
                                        <option value="Handebol" <?php echo ($perfil['modalidade'] ?? '') === 'Handebol' ? 'selected' : ''; ?>>Handebol</option>
                                        <option value="Natação" <?php echo ($perfil['modalidade'] ?? '') === 'Natação' ? 'selected' : ''; ?>>Natação</option>
                                        <option value="Atletismo" <?php echo ($perfil['modalidade'] ?? '') === 'Atletismo' ? 'selected' : ''; ?>>Atletismo</option>
                                        <option value="Outros" <?php echo ($perfil['modalidade'] ?? '') === 'Outros' ? 'selected' : ''; ?>>Outros</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" name="cidade" class="form-control" value="<?php echo htmlspecialchars($perfil['cidade'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <input type="text" name="estado" class="form-control" value="<?php echo htmlspecialchars($perfil['estado'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">País</label>
                                    <input type="text" name="pais" class="form-control" value="<?php echo htmlspecialchars($perfil['pais'] ?? ''); ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Sua Bio (Sobre você)</label>
                                    <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($perfil['bio'] ?? ''); ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Histórico de Campeonatos</label>
                                    <textarea name="historico_campeonatos" class="form-control" rows="3"><?php echo htmlspecialchars($perfil['historico_campeonatos'] ?? ''); ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">E-mail</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" placeholder="seu@email.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nova senha</label>
                                    <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para manter a atual">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Instagram</label>
                                    <input type="url" name="instagram_link" class="form-control" value="<?php echo htmlspecialchars($perfil['instagram_link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">TikTok</label>
                                    <input type="url" name="tiktok_link" class="form-control" value="<?php echo htmlspecialchars($perfil['tiktok_link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">YouTube</label>
                                    <input type="url" name="youtube_link" class="form-control" value="<?php echo htmlspecialchars($perfil['youtube_link'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Currículo (PDF)</label>
                                    <input type="url" name="curriculo_link" class="form-control" value="<?php echo htmlspecialchars($perfil['curriculo_link'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-fya btn-lg px-5 py-3">Salvar Perfil Completo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-4">
                <div class="col-lg-8">
                    <div class="edit-card">
                        <h4 class="fw-bold mb-4">Nova Publicação</h4>
                        <form action="../controllers/post_controller.php?action=create" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" name="titulo_publicacao" class="form-control" placeholder="Título da publicação">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea name="descricao_publicacao" class="form-control" rows="3" placeholder="Escreva o conteúdo da publicação"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Imagem (opcional)</label>
                                <input type="file" name="imagem_publicacao" class="form-control" accept="image/*">
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-fya px-4 py-2">Publicar Agora</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { document.getElementById('preview').src = e.target.result; }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
