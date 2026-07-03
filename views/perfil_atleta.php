<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/perfil_atleta.php
 * Descrição: Perfil do atleta (visualização própria e de terceiros).
 */
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = $_SESSION['user_id'];

// Ver qual usuário visualizar (por querystring ?id=)
$viewUserId = intval($_GET['id'] ?? 0);
if ($viewUserId <= 0) { $viewUserId = $currentUserId; }

try {
    $stmt = $pdo->prepare(
        "SELECT u.id AS usuario_id, u.nome, u.email, p.* 
         FROM usuarios u 
         LEFT JOIN atletas_perfil p ON u.id = p.id_usuario 
         WHERE u.id = ?"
    );
    $stmt->execute([$viewUserId]);
    $perfil = $stmt->fetch();

    if (!$perfil && $viewUserId === $currentUserId) {
        // cria perfil automático para o proprietário caso não exista
        $stmt = $pdo->prepare("INSERT INTO atletas_perfil (id_usuario) VALUES (?)");
        $stmt->execute([$currentUserId]);
        header("Refresh:0");
        exit();
    }

    if (!$perfil) {
        $perfil = [
            'usuario_id' => $viewUserId,
            'nome' => 'Usuário sem perfil',
            'email' => '',
            'bio' => '',
            'idade' => null,
            'peso' => null,
            'altura' => null,
            'foto_perfil' => null,
            'nota_media' => 0
        ];
    }
} catch (PDOException $e) {
    die('Erro ao carregar perfil: ' . $e->getMessage());
}

// Publicações do usuário visualizado
try {
    $stmtPosts = $pdo->prepare("SELECT * FROM publicacoes WHERE id_usuario = ? ORDER BY data_criacao DESC");
    $stmtPosts->execute([$viewUserId]);
    $publicacoes = $stmtPosts->fetchAll();
} catch (PDOException $e) {
    $publicacoes = [];
}

try {
    $stmtLikes = $pdo->prepare(
        "SELECT COUNT(*) FROM likes l JOIN publicacoes p ON p.id = l.id_publicacao WHERE p.id_usuario = ?"
    );
    $stmtLikes->execute([$viewUserId]);
    $likesRecebidos = (int)$stmtLikes->fetchColumn();
} catch (PDOException $e) {
    $likesRecebidos = 0;
}

try {
    $stmtMsgs = $pdo->prepare("SELECT COUNT(*) FROM mensagens WHERE id_destinatario = ?");
    $stmtMsgs->execute([$viewUserId]);
    $mensagensRecebidas = (int)$stmtMsgs->fetchColumn();
} catch (PDOException $e) {
    $mensagensRecebidas = 0;
}

$camposPreenchidos = 0;
$camposTotais = 8;
foreach ([
    $perfil['foto_perfil'] ?? null,
    $perfil['posicao'] ?? null,
    $perfil['modalidade'] ?? null,
    $perfil['cidade'] ?? null,
    $perfil['estado'] ?? null,
    $perfil['bio'] ?? null,
    $perfil['instagram_link'] ?? null,
    $perfil['curriculo_link'] ?? null,
] as $valor) {
    if (!empty($valor)) {
        $camposPreenchidos++;
    }
}
$perfilCompletoPercent = (int)round(($camposPreenchidos / $camposTotais) * 100);
$badges = [];
if (!empty($publicacoes)) { $badges[] = ['titulo' => 'Ativo', 'texto' => 'Publicou recentemente']; }
if ($perfilCompletoPercent >= 80) { $badges[] = ['titulo' => 'Perfil completo', 'texto' => 'Seu perfil está bem preenchido']; }
if (!empty($perfil['instagram_link']) || !empty($perfil['youtube_link']) || !empty($perfil['tiktok_link'])) { $badges[] = ['titulo' => 'Presença digital', 'texto' => 'Redes sociais vinculadas']; }
if ($likesRecebidos > 0) { $badges[] = ['titulo' => 'Relevância', 'texto' => 'Recebeu curtidas nas publicações']; }

$isOwner = ($viewUserId === $currentUserId);

// foto do perfil (verifica arquivo antes)
if (!empty($perfil['foto_perfil']) && file_exists(__DIR__ . '/../uploads/' . $perfil['foto_perfil'])) {
    $foto = '../uploads/' . $perfil['foto_perfil'];
} else {
    $foto = 'https://ui-avatars.com/api/?name=' . urlencode($perfil['nome']) . '&background=9ACD32&color=fff';
}

$alertMessage = '';
if (!empty($_GET['msg'])) { $alertMessage = htmlspecialchars($_GET['msg']); }
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --fya-primary: #9ACD32; --transition-speed: .3s; }
        html,body{margin:0;padding:0}
        body { font-family: 'Inter', sans-serif; }
        .profile-cover { height:200px; width:100%; background: #ddd center/cover no-repeat; border-radius:0 0 20px 20px; }
        .profile-avatar { width:130px; height:130px; border-radius:50%; border:5px solid var(--bs-body-bg); object-fit:cover; box-shadow:0 4px 10px rgba(0,0,0,.15); }
        .section-card { background-color: var(--bs-tertiary-bg); border:none; border-radius:12px; padding:1.5rem; box-shadow:0 4px 12px rgba(0,0,0,0.04); }
        .summary-card { border:1px solid rgba(154,205,50,0.2); border-radius:14px; padding:1rem; background: linear-gradient(135deg, rgba(154,205,50,0.14), rgba(255,255,255,0.02)); }
        .summary-card .value { font-size:1.35rem; font-weight:700; color: var(--fya-primary); }
        .activity-item { border-left:3px solid var(--fya-primary); padding-left:0.8rem; margin-bottom:0.8rem; }
        .activity-pill { border-radius:999px; background-color: rgba(154,205,50,0.12); color: var(--fya-primary); padding:0.25rem 0.7rem; font-size:0.75rem; }
        #main-content { padding-top:70px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main class="container-fluid p-4">
            <?php if ($alertMessage): ?>
                <div class="alert alert-success"><?php echo $alertMessage; ?></div>
            <?php endif; ?>

            <section class="profile-header mb-5 position-relative">
                <div class="profile-cover"></div>
                <div class="position-absolute" style="top:120px; left:30px;">
                    <img src="<?php echo $foto; ?>" alt="Avatar" class="profile-avatar">
                </div>
                <div class="position-absolute" style="top:140px; right:30px;">
                    <?php if ($isOwner): ?>
                        <a href="editar_perfil.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Editar perfil</a>
                    <?php else: ?>
                        <a href="mensagens.php?contact=<?php echo intval($viewUserId); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chat-left-text"></i> Enviar mensagem</a>
                    <?php endif; ?>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="section-card">
                        <h4 class="fw-bold"><?php echo htmlspecialchars($perfil['nome']); ?></h4>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($perfil['email']); ?></p>
                        <div class="mb-3">
                            <strong>Sobre</strong>
                            <p class="text-muted small"><?php echo !empty($perfil['bio']) ? nl2br(htmlspecialchars($perfil['bio'])) : 'Nenhuma bio adicionada.'; ?></p>
                        </div>
                        <div class="row g-2">
                            <div class="col-6"><small class="text-muted">Idade</small><div><?php echo $perfil['idade'] ?? '--'; ?></div></div>
                            <div class="col-6"><small class="text-muted">Altura</small><div><?php echo $perfil['altura'] ?? '--'; ?></div></div>
                        </div>
                        <div class="row g-2 mt-3">
                            <div class="col-6"><small class="text-muted">Modalidade</small><div><?php echo htmlspecialchars($perfil['modalidade'] ?: '--'); ?></div></div>
                            <div class="col-6"><small class="text-muted">Localização</small><div><?php echo htmlspecialchars(trim(($perfil['cidade'] ?: '') . ($perfil['cidade'] && $perfil['estado'] ? ', ' : '') . ($perfil['estado'] ?: '') . ($perfil['pais'] ? ' - ' : '') . ($perfil['pais'] ?: '')) ?: '--'); ?></div></div>
                        </div>
                        <?php if (!empty($perfil['historico_campeonatos'])): ?>
                        <div class="mt-4">
                            <strong>Histórico de Campeonatos</strong>
                            <p class="text-muted small mb-0"><?php echo nl2br(htmlspecialchars($perfil['historico_campeonatos'])); ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="mt-4">
                            <strong>Redes Sociais</strong>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php if (!empty($perfil['instagram_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($perfil['instagram_link']); ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-instagram"></i> Instagram</a>
                                <?php endif; ?>
                                <?php if (!empty($perfil['tiktok_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($perfil['tiktok_link']); ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-tiktok"></i> TikTok</a>
                                <?php endif; ?>
                                <?php if (!empty($perfil['youtube_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($perfil['youtube_link']); ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-youtube"></i> YouTube</a>
                                <?php endif; ?>
                                <?php if (!empty($perfil['curriculo_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($perfil['curriculo_link']); ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-file-earmark-person"></i> Currículo</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="section-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0">Progresso do perfil</h5>
                            <span class="badge bg-success bg-opacity-10 text-success"><?php echo $perfilCompletoPercent; ?>%</span>
                        </div>
                        <div class="progress rounded-pill mb-2" style="height: 10px;">
                            <div class="progress-bar" style="width: <?php echo $perfilCompletoPercent; ?>%; background-color: var(--fya-primary);"></div>
                        </div>
                        <div class="small text-muted">Complete links, localização e currículo para aumentar sua visibilidade e atrair mais oportunidades.</div>
                    </div>

                    <div class="section-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0">Resumo rápido</h4>
                            <span class="badge bg-success bg-opacity-10 text-success">Perfil <?php echo $perfilCompletoPercent; ?>%</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <div class="summary-card text-center">
                                    <div class="value"><?php echo count($publicacoes); ?></div>
                                    <div class="small text-muted">Publicações</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="summary-card text-center">
                                    <div class="value"><?php echo $likesRecebidos; ?></div>
                                    <div class="small text-muted">Curtidas</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="summary-card text-center">
                                    <div class="value"><?php echo $mensagensRecebidas; ?></div>
                                    <div class="small text-muted">Mensagens</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="summary-card text-center">
                                    <div class="value"><?php echo $perfilCompletoPercent; ?>%</div>
                                    <div class="small text-muted">Completo</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($badges)): ?>
                        <div class="section-card mb-4">
                            <h5 class="fw-bold mb-3">Conquistas e destaque</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($badges as $badge): ?>
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2">
                                        <strong><?php echo htmlspecialchars($badge['titulo']); ?></strong> · <?php echo htmlspecialchars($badge['texto']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="section-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0">Atividades recentes</h4>
                            <span class="activity-pill">Atualizado agora</span>
                        </div>
                        <div class="activity-item">
                            <div class="fw-semibold">Perfil em destaque</div>
                            <div class="text-muted small">Seu perfil está disponível para visualização por clubes e olheiros.</div>
                        </div>
                        <div class="activity-item">
                            <div class="fw-semibold">Última publicação</div>
                            <div class="text-muted small"><?php echo !empty($publicacoes) ? htmlspecialchars($publicacoes[0]['titulo'] ?: 'Publicação recente') : 'Ainda não há publicações'; ?></div>
                        </div>
                        <div class="activity-item">
                            <div class="fw-semibold">Engajamento</div>
                            <div class="text-muted small">Você recebeu <?php echo $likesRecebidos; ?> curtidas e <?php echo $mensagensRecebidas; ?> mensagens.</div>
                        </div>
                    </div>

                    <div class="section-card">
                        <h4 class="fw-bold mb-4">Publicações Recentes</h4>

                        <?php if (!empty($publicacoes)): ?>
                            <div class="row g-4">
                                <?php foreach ($publicacoes as $post): ?>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <?php
                                                $postImg = '';
                                                if (!empty($post['imagem']) && file_exists(__DIR__ . '/../uploads/' . $post['imagem'])) {
                                                    $postImg = '../uploads/' . $post['imagem'];
                                                }
                                            ?>
                                            <?php if ($postImg): ?>
                                                <img src="<?php echo $postImg; ?>" class="card-img-top" style="height:220px; object-fit:cover;" alt="<?php echo htmlspecialchars($post['titulo'] ?: 'Publicação'); ?>">
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($post['titulo'] ?: 'Sem título'); ?></h5>
                                                    <?php if ($isOwner): ?>
                                                        <a href="../controllers/post_controller.php?action=delete&id=<?php echo intval($post['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir publicação?');"><i class="bi bi-trash"></i></a>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-muted small"><?php echo nl2br(htmlspecialchars($post['descricao'] ?: '')); ?></p>
                                            </div>
                                            <div class="card-footer bg-body small d-flex justify-content-between align-items-center">
                                                <span><?php echo date('d/m/Y H:i', strtotime($post['data_criacao'])); ?></span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php
                                                        try {
                                                            $likesCount = 0;
                                                            $likedByUser = false;
                                                            $stmtLikes = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE id_publicacao = ?');
                                                            $stmtLikes->execute([$post['id']]);
                                                            $likesCount = (int)$stmtLikes->fetchColumn();

                                                            $stmtLiked = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE id_publicacao = ? AND id_usuario = ?');
                                                            $stmtLiked->execute([$post['id'], $currentUserId]);
                                                            $likedByUser = $stmtLiked->fetchColumn() > 0;
                                                        } catch (PDOException $e) {
                                                            $likesCount = 0; $likedByUser = false;
                                                        }
                                                    ?>
                                                    <a href="../controllers/post_controller.php?action=like&id=<?php echo intval($post['id']); ?>" class="text-decoration-none">
                                                        <i class="bi <?php echo $likedByUser ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                    </a>
                                                    <small class="text-muted"><?php echo $likesCount; ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Nenhuma publicação encontrada.</p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
