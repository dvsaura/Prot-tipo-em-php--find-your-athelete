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

try {
    $stmtFollowers = $pdo->prepare('SELECT COUNT(*) FROM follows WHERE id_seguido = ?');
    $stmtFollowers->execute([$viewUserId]);
    $followersCount = (int)$stmtFollowers->fetchColumn();

    $stmtFollowing = $pdo->prepare('SELECT COUNT(*) FROM follows WHERE id_seguidor = ?');
    $stmtFollowing->execute([$viewUserId]);
    $followingCount = (int)$stmtFollowing->fetchColumn();

    $stmtFollowingUser = $pdo->prepare('SELECT id FROM follows WHERE id_seguidor = ? AND id_seguido = ?');
    $stmtFollowingUser->execute([$currentUserId, $viewUserId]);
    $isFollowing = (bool)$stmtFollowingUser->fetch();
} catch (PDOException $e) {
    $followersCount = 0;
    $followingCount = 0;
    $isFollowing = false;
}

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
        :root { --fya-primary: #070D1B; --fya-secondary: #18233F; --fya-accent: #F08000; --transition-speed: .3s; }
        html,body{margin:0;padding:0}
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fc; }
        .profile-cover { height:220px; width:100%; background: linear-gradient(135deg, var(--fya-secondary), var(--fya-primary)); border-radius:0 0 24px 24px; }
        .profile-avatar { width:128px; height:128px; border-radius:50%; border:5px solid #fff; object-fit:cover; box-shadow:0 8px 24px rgba(0,0,0,.18); }
        .section-card { background-color: #fff; border:none; border-radius:16px; padding:1.25rem; box-shadow:0 6px 18px rgba(0,0,0,0.05); }
        .stat-pill { display:flex; align-items:center; justify-content:center; gap:.35rem; padding:.7rem .9rem; border-radius:999px; background:#f3f5f9; color:var(--fya-secondary); font-size:.95rem; }
        .stat-pill strong { color:var(--fya-primary); }
        .btn-fya { background-color: var(--fya-accent); color:#fff; border:none; }
        .btn-fya:hover { background-color:#d96d00; color:#fff; }
        #main-content { padding-top:70px; }
        @media (max-width: 576px) {
            .profile-avatar { width:104px; height:104px; }
            .profile-header { text-align:center; }
            .profile-actions { justify-content:center !important; }
        }
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

            <section class="profile-header mb-4 position-relative">
                <div class="profile-cover"></div>
                <div class="position-absolute start-0 end-0" style="top:90px;">
                    <div class="container-fluid px-3 px-md-4">
                        <div class="row align-items-end g-3">
                            <div class="col-12 col-md-4 text-center text-md-start">
                                <img src="<?php echo $foto; ?>" alt="Avatar" class="profile-avatar">
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-end gap-3">
                                    <div class="text-center text-md-start">
                                        <h3 class="fw-bold mb-1 text-white"><?php echo htmlspecialchars($perfil['nome']); ?></h3>
                                        <p class="text-light mb-2 small"><?php echo htmlspecialchars($perfil['email']); ?></p>
                                        <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2 mb-2">
                                            <span class="stat-pill"><strong><?php echo count($publicacoes); ?></strong> publicações</span>
                                            <span class="stat-pill"><strong><?php echo $followersCount; ?></strong> seguidores</span>
                                            <span class="stat-pill"><strong><?php echo $followingCount; ?></strong> seguindo</span>
                                            <span class="stat-pill"><strong><?php echo $likesRecebidos; ?></strong> curtidas</span>
                                        </div>
                                    </div>
                                    <div class="profile-actions d-flex flex-wrap justify-content-center gap-2">
                                        <?php if ($isOwner): ?>
                                            <button class="btn btn-fya btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovaPublicacao"><i class="bi bi-plus-lg"></i> Nova Publicação</button>
                                            <a href="editar_perfil.php" class="btn btn-outline-light btn-sm"><i class="bi bi-pencil"></i> Editar perfil</a>
                                        <?php else: ?>
                                            <a href="mensagens.php?contact=<?php echo intval($viewUserId); ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-chat-left-text"></i> Enviar mensagem</a>
                                            <a href="../controllers/post_controller.php?action=follow&user_id=<?php echo intval($viewUserId); ?>" class="btn btn-sm <?php echo $isFollowing ? 'btn-outline-danger' : 'btn-fya'; ?>">
                                                <i class="bi bi-person-plus"></i> <?php echo $isFollowing ? 'Deixar de seguir' : 'Seguir'; ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="section-card">
                        <h5 class="fw-bold mb-3">Informações básicas</h5>
                        <p class="text-muted small mb-3"><?php echo !empty($perfil['bio']) ? nl2br(htmlspecialchars($perfil['bio'])) : 'Nenhuma bio adicionada.'; ?></p>
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
                            <div class="progress-bar" style="width: <?php echo $perfilCompletoPercent; ?>%; background-color: var(--fya-accent);"></div>
                        </div>
                        <div class="small text-muted">Complete links, localização e currículo para aumentar sua visibilidade e atrair mais oportunidades.</div>
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
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>
                                                            <ul class="dropdown-menu">
                                                                <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#modalEditarPost_<?php echo intval($post['id']); ?>">Editar Publicação</button></li>
                                                                <li><a class="dropdown-item text-danger" href="../controllers/post_controller.php?action=delete&id=<?php echo intval($post['id']); ?>" onclick="return confirm('Excluir publicação?');">Excluir</a></li>
                                                            </ul>
                                                        </div>
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

                                                            $stmtCommentsCount = $pdo->prepare('SELECT COUNT(*) FROM comentarios WHERE id_publicacao = ?');
                                                            $stmtCommentsCount->execute([$post['id']]);
                                                            $commentsCount = (int)$stmtCommentsCount->fetchColumn();
                                                        } catch (PDOException $e) {
                                                            $likesCount = 0; $likedByUser = false; $commentsCount = 0;
                                                        }
                                                    ?>
                                                    <a href="../controllers/post_controller.php?action=like&id=<?php echo intval($post['id']); ?>" class="text-decoration-none">
                                                        <i class="bi <?php echo $likedByUser ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                    </a>
                                                    <small class="text-muted"><?php echo $likesCount; ?></small>
                                                    <span class="text-muted">•</span>
                                                    <small class="text-muted"><i class="bi bi-chat-left-text"></i> <?php echo $commentsCount; ?></small>
                                                </div>
                                            </div>
                                            <div class="card-body border-top">
                                                <form action="../controllers/post_controller.php?action=comment" method="POST" class="mt-2">
                                                    <input type="hidden" name="post_id" value="<?php echo intval($post['id']); ?>">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="comentario" class="form-control" placeholder="Adicionar comentário..." required>
                                                        <button type="submit" class="btn btn-outline-secondary">Enviar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($isOwner): ?>
                                    <div class="modal fade" id="modalEditarPost_<?php echo intval($post['id']); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header border-0">
                                                    <h5 class="modal-title fw-bold">Editar Publicação</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="../controllers/post_controller.php?action=edit" method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="post_id" value="<?php echo intval($post['id']); ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Legenda</label>
                                                            <input type="text" name="titulo_publicacao" class="form-control" value="<?php echo htmlspecialchars($post['titulo'] ?: ''); ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Bio/Descrição</label>
                                                            <textarea name="descricao_publicacao" class="form-control" rows="3"><?php echo htmlspecialchars($post['descricao'] ?: ''); ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Trocar imagem</label>
                                                            <input type="file" name="imagem_publicacao" class="form-control" accept="image/*">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-fya">Salvar Alterações</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Nenhuma publicação encontrada.</p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <?php if ($isOwner): ?>
            <div class="modal fade" id="modalNovaPublicacao" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Nova Publicação</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="../controllers/post_controller.php?action=create" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Título</label>
                                    <input type="text" name="titulo_publicacao" class="form-control" placeholder="Título da publicação">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Descrição</label>
                                    <textarea name="descricao_publicacao" class="form-control" rows="3" placeholder="Escreva o conteúdo da publicação"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Imagem (opcional)</label>
                                    <input type="file" name="imagem_publicacao" class="form-control" accept="image/*">
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-fya">Publicar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
