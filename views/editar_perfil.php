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

$stmt = $pdo->prepare("SELECT * FROM atletas_perfil WHERE id_usuario = ?");
$stmt->execute([$userId]);
$perfil = $stmt->fetch();

// --- PROCESSAMENTO DO FORMULÁRIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posicao = $_POST['posicao'] ?? '';
    $idade = $_POST['idade'] ?? 0;
    $peso = $_POST['peso'] ?? 0;
    $altura = $_POST['altura'] ?? 0;
    $bio = $_POST['bio'] ?? '';
    $youtube = $_POST['youtube_link'] ?? '';
    $tiktok = $_POST['tiktok_link'] ?? '';
    $instagram = $_POST['instagram_link'] ?? '';
    $curriculo = $_POST['curriculo_link'] ?? '';
    
    // Atributos Técnicos
    $vel = $_POST['velocidade'] ?? 0;
    $tec = $_POST['tecnica'] ?? 0;
    $fis = $_POST['fisico'] ?? 0;
    $vis = $_POST['visao_jogo'] ?? 0;

    try {
        // 1. Upload de Foto de Perfil
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
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

        // 2. Atualização de todos os dados incluindo Atributos
        $stmt = $pdo->prepare(" 
            UPDATE atletas_perfil 
            SET posicao = ?, idade = ?, peso = ?, altura = ?, bio = ?, 
                youtube_link = ?, tiktok_link = ?, instagram_link = ?, curriculo_link = ?,
                velocidade = ?, tecnica = ?, fisico = ?, visao_jogo = ?
            WHERE id_usuario = ?
        ");
        $stmt->execute([$posicao, $idade, $peso, $altura, $bio, $youtube, $tiktok, $instagram, $curriculo, $vel, $tec, $fis, $vis, $userId]);

        $stmt = $pdo->prepare("SELECT * FROM atletas_perfil WHERE id_usuario = ?");
        $stmt->execute([$userId]);
        $perfil = $stmt->fetch();

        $message = "<div class='alert alert-success'>Perfil e atributos atualizados com sucesso!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Erro ao salvar: " . $e->getMessage() . "</div>";
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
                                <img src="<?php echo !empty($perfil['foto_perfil']) ? '../uploads/'.$perfil['foto_perfil'] : 'https://i.pravatar.cc/300?u='.$userId; ?>" class="avatar-preview" id="preview">
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

                                <div class="col-12">
                                    <label class="form-label">Sua Bio (Sobre você)</label>
                                    <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($perfil['bio'] ?? ''); ?></textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5 class="fw-bold mb-3">Nível Técnico (0 a 100)</h5>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between"><label>Velocidade</label> <span id="val_vel"><?php echo $perfil['velocidade']; ?>%</span></div>
                                        <input type="range" name="velocidade" class="form-range attr-slider" min="0" max="100" value="<?php echo $perfil['velocidade']; ?>" oninput="document.getElementById('val_vel').innerText = this.value + '%'"></input>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between"><label>Técnica</label> <span id="val_tec"><?php echo $perfil['tecnica']; ?>%</span></div>
                                        <input type="range" name="tecnica" class="form-range attr-slider" min="0" max="100" value="<?php echo $perfil['tecnica']; ?>" oninput="document.getElementById('val_tec').innerText = this.value + '%'"></input>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between"><label>Físico</label> <span id="val_fis"><?php echo $perfil['fisico']; ?>%</span></div>
                                        <input type="range" name="fisico" class="form-range attr-slider" min="0" max="100" value="<?php echo $perfil['fisico']; ?>" oninput="document.getElementById('val_fis').innerText = this.value + '%'"></input>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between"><label>Visão de Jogo</label> <span id="val_vis"><?php echo $perfil['visao_jogo']; ?>%</span></div>
                                        <input type="range" name="visao_jogo" class="form-range attr-slider" min="0" max="100" value="<?php echo $perfil['visao_jogo']; ?>" oninput="document.getElementById('val_vis').innerText = this.value + '%'"></input>
                                    </div>
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
