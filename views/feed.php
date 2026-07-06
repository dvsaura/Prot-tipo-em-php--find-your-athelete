<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/feed.php
 * Descrição: Painel inicial com interface limpa e responsiva.
 */
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $stmtFeed = $pdo->prepare(
        'SELECT p.*, u.nome AS autor, u.id AS autor_id, a.foto_perfil, a.modalidade, a.cidade, a.estado, a.pais ' .
        'FROM publicacoes p ' .
        'JOIN usuarios u ON u.id = p.id_usuario ' .
        'LEFT JOIN atletas_perfil a ON a.id_usuario = u.id ' .
        'ORDER BY p.data_criacao DESC LIMIT 8'
    );
    $stmtFeed->execute();
    $publicacoesFeed = $stmtFeed->fetchAll();
} catch (PDOException $e) {
    $publicacoesFeed = [];
}

try {
    $stmtAtletas = $pdo->prepare(
        'SELECT u.id, u.nome, u.tipo_conta, a.posicao, a.idade, a.velocidade, a.tecnica, a.fisico, a.visao_jogo, a.foto_perfil ' .
        'FROM usuarios u ' .
        'LEFT JOIN atletas_perfil a ON a.id_usuario = u.id ' .
        'WHERE u.id != ? AND u.tipo_conta = "atleta" ' .
        'ORDER BY u.data_criacao DESC LIMIT 8'
    );
    $stmtAtletas->execute([$_SESSION['user_id']]);
    $atletasFeed = $stmtAtletas->fetchAll();
} catch (PDOException $e) {
    $atletasFeed = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Feed Inicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --fya-primary: #070D1B;
            --fya-secondary: #18233F;
            --fya-accent: #F08000;
            --transition-speed: 0.3s;
        }

        html, body {
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bs-body-bg);
            color: var(--fya-primary);
            transition: background-color var(--transition-speed);
        }

        .btn-fya {
            background-color: var(--fya-accent);
            color: #fff;
            font-weight: 600;
            border: none;
        }

        .btn-fya:hover {
            background-color: #d96d00;
            color: #fff;
        }

        .featured-card, .athlete-card {
            background-color: var(--bs-tertiary-bg);
            border: none;
            border-radius: 18px;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            overflow: hidden;
        }

        .featured-card:hover, .athlete-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(7, 13, 27, 0.12);
        }

        .feed-highlight {
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(24, 35, 63, 0.14), rgba(240, 128, 0, 0.08));
            border: 1px solid rgba(24, 35, 63, 0.1);
        }

        .section-title {
            font-size: clamp(1.08rem, 1.8vw, 1.35rem);
            line-height: 1.2;
        }

        .card-actions {
            border-top: 1px solid var(--bs-border-color);
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0 0;
            gap: 0.5rem;
        }

        .btn-action {
            color: var(--fya-secondary);
            transition: color var(--transition-speed);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-action:hover {
            color: var(--fya-accent);
        }

        #main-content {
            padding-top: 70px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main class="container-fluid p-3 p-md-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="mb-4 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2">
                    <div>
                        <h3 class="fw-bold mb-1">Bem-vindo ao seu feed</h3>
                        <p class="text-muted mb-0">Mantenha seu perfil ativo e mostre seu trabalho para olheiros e clubes.</p>
                    </div>
                    <a href="nova_publicacao.php" class="btn btn-fya btn-lg">Adicionar publicação</a>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, rgba(24, 35, 63, 0.16), rgba(255,255,255,0.04));">
                        <div class="card-body">
                            <div class="small text-muted">Publicações</div>
                            <div class="fw-bold fs-4"><?php echo count($publicacoesFeed); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="small text-muted">Talentos</div>
                            <div class="fw-bold fs-4"><?php echo count($atletasFeed); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="small text-muted">Buscar</div>
                            <div class="fw-bold fs-4"><a href="buscar_atletas.php" class="text-decoration-none">Explorar</a></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="small text-muted">Mensagens</div>
                            <div class="fw-bold fs-4"><a href="mensagens.php" class="text-decoration-none">Conversar</a></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="feed-highlight p-3 mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-8">
                        <div class="fw-bold mb-1">Seu perfil está ganhando força</div>
                        <div class="small text-muted">Mantenha uma rotina de publicações, atualize links e mostre seu melhor trabalho para clubes e olheiros.</div>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end">
                        <div class="d-flex flex-wrap justify-content-start justify-content-lg-end gap-2">
                            <a href="editar_perfil.php" class="btn btn-sm btn-fya">Completar perfil</a>
                            <a href="nova_publicacao.php" class="btn btn-sm btn-outline-secondary">Nova publicação</a>
                        </div>
                    </div>
                </div>
            </div>

            <section class="mb-5">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-2">
                    <h3 class="fw-bold m-0 section-title">Publicações</h3>
                    <a href="buscar_atletas.php" class="btn btn-sm btn-outline-secondary">Ver Todos</a>
                </div>

                <?php if (!empty($publicacoesFeed)): ?>
                    <div class="row g-4">
                        <?php foreach ($publicacoesFeed as $post): ?>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="card featured-card h-100">
                                    <?php
                                        $uploadDir = __DIR__ . '/../uploads/';
                                        if (!empty($post['imagem']) && file_exists($uploadDir . $post['imagem'])):
                                            $postImgSrc = '../uploads/' . htmlspecialchars($post['imagem']);
                                        else:
                                            $postImgSrc = '';
                                        endif;
                                    ?>
                                    <?php if (!empty($postImgSrc)): ?>
                                        <img src="<?php echo $postImgSrc; ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($post['titulo'] ?: 'Publicação'); ?>">
                                    <?php else: ?>
                                        <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="bi bi-image fs-1 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($post['titulo'] ?: 'Sem título'); ?></h5>
                                        <p class="card-text text-muted small mb-3"><?php echo nl2br(htmlspecialchars(mb_strimwidth($post['descricao'] ?: 'Publicação sem descrição.', 0, 100, '...'))); ?></p>
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <?php
                                                $authorImg = '';
                                                if (!empty($post['foto_perfil']) && file_exists(__DIR__ . '/../uploads/' . $post['foto_perfil'])) {
                                                    $authorImg = '../uploads/' . htmlspecialchars($post['foto_perfil']);
                                                }
                                            ?>
                                            <a href="perfil_atleta.php?id=<?php echo intval($post['autor_id']); ?>" class="text-decoration-none">
                                                <img src="<?php echo $authorImg ?: 'https://ui-avatars.com/api/?name='.urlencode($post['autor']).'&background=070D1B&color=fff'; ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="Autor">
                                            </a>
                                            <div>
                                                <a href="perfil_atleta.php?id=<?php echo intval($post['autor_id']); ?>" class="text-decoration-none text-body fw-semibold">
                                                    <small class="d-block fw-semibold"><?php echo htmlspecialchars($post['autor']); ?></small>
                                                </a>
                                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($post['data_criacao'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mb-3 small text-muted">
                                            <?php if (!empty($post['modalidade'])): ?>
                                                <span class="badge bg-dark bg-opacity-10 text-dark"><?php echo htmlspecialchars($post['modalidade']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($post['cidade']) || !empty($post['estado']) || !empty($post['pais'])): ?>
                                                <span><?php echo htmlspecialchars(trim(($post['cidade'] ?: '') . ($post['cidade'] && $post['estado'] ? ', ' : '') . ($post['estado'] ?: '') . ($post['pais'] ? ' - ' : '') . ($post['pais'] ?: ''))); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-actions justify-content-between">
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="mensagens.php?contact=<?php echo intval($post['autor_id']); ?>" class="btn-action"><i class="bi bi-chat-left-text"></i> Conversar</a>
                                                <a href="perfil_atleta.php?id=<?php echo intval($post['autor_id']); ?>" class="btn-action"><i class="bi bi-eye"></i> Perfil</a>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php
                                                    try {
                                                        $likesCount = 0;
                                                        $likedByUser = false;
                                                        $stmtLikes = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE id_publicacao = ?');
                                                        $stmtLikes->execute([$post['id']]);
                                                        $likesCount = (int)$stmtLikes->fetchColumn();

                                                        $stmtLiked = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE id_publicacao = ? AND id_usuario = ?');
                                                        $stmtLiked->execute([$post['id'], $_SESSION['user_id']]);
                                                        $likedByUser = $stmtLiked->fetchColumn() > 0;

                                                        $stmtComments = $pdo->prepare('SELECT COUNT(*) FROM comentarios WHERE id_publicacao = ?');
                                                        $stmtComments->execute([$post['id']]);
                                                        $commentsCount = (int)$stmtComments->fetchColumn();

                                                        $stmtCommentsList = $pdo->prepare(
                                                            'SELECT c.comentario, c.data_criacao, u.nome ' .
                                                            'FROM comentarios c ' .
                                                            'JOIN usuarios u ON u.id = c.id_usuario ' .
                                                            'WHERE c.id_publicacao = ? ' .
                                                            'ORDER BY c.data_criacao DESC LIMIT 3'
                                                        );
                                                        $stmtCommentsList->execute([$post['id']]);
                                                        $postComments = $stmtCommentsList->fetchAll();
                                                    } catch (PDOException $e) {
                                                        $likesCount = 0;
                                                        $likedByUser = false;
                                                        $commentsCount = 0;
                                                        $postComments = [];
                                                    }
                                                ?>
                                                <a href="../controllers/post_controller.php?action=like&id=<?php echo intval($post['id']); ?>" class="btn-action" title="Curtir">
                                                    <i class="bi <?php echo $likedByUser ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                </a>
                                                <small class="text-muted"><?php echo $likesCount; ?></small>
                                                <span class="mx-2 text-muted">•</span>
                                                <span class="text-muted small"><i class="bi bi-chat-left-text"></i> <?php echo $commentsCount; ?></span>
                                            </div>
                                        </div>
                                        <?php if (!empty($postComments)): ?>
                                            <div class="mt-2 px-3 pb-2">
                                                <div class="small text-muted fw-semibold mb-2">Comentários</div>
                                                <?php foreach ($postComments as $comment): ?>
                                                    <div class="small text-muted mb-1">
                                                        <strong><?php echo htmlspecialchars($comment['nome']); ?></strong>: <?php echo htmlspecialchars($comment['comentario']); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <form action="../controllers/post_controller.php?action=comment" method="POST" class="px-3 pb-3">
                                            <input type="hidden" name="post_id" value="<?php echo intval($post['id']); ?>">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="comentario" class="form-control" placeholder="Escreva um comentário..." required>
                                                <button type="submit" class="btn btn-outline-secondary">Comentar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhuma publicação foi encontrada no feed ainda. Publique algo no seu perfil para começar.</div>
                <?php endif; ?>
            </section>

            <section>
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h3 class="fw-bold m-0 section-title">Novos Talentos</h3>
                </div>

                <?php if (!empty($atletasFeed)): ?>
                    <div class="row g-4">
                        <?php foreach ($atletasFeed as $atleta): ?>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="card athlete-card h-100">
                                    <div style="height: 220px; overflow: hidden;">
                                        <img src="<?php echo !empty($atleta['foto_perfil']) ? '../uploads/'.htmlspecialchars($atleta['foto_perfil']) : 'https://ui-avatars.com/api/?name='.urlencode($atleta['nome']).'&background=070D1B&color=fff'; ?>" alt="<?php echo htmlspecialchars($atleta['nome']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($atleta['nome']); ?></h5>
                                        <div class="text-muted small mb-3"><?php echo htmlspecialchars(($atleta['posicao'] ?: 'Atleta') . ' • ' . ($atleta['idade'] ? $atleta['idade'] . ' anos' : 'Idade não informada')); ?></div>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <?php if (!empty($atleta['modalidade'])): ?><span class="badge bg-dark bg-opacity-10 text-dark"><?php echo htmlspecialchars($atleta['modalidade']); ?></span><?php endif; ?>
                                            <?php if (!empty($atleta['velocidade'])): ?><span class="badge bg-dark bg-opacity-10 text-dark"><?php echo htmlspecialchars($atleta['velocidade']); ?> vel.</span><?php endif; ?>
                                        </div>
                                        <div class="card-actions justify-content-between">
                                            <a href="perfil_atleta.php?id=<?php echo intval($atleta['id']); ?>" class="btn-action"><i class="bi bi-eye"></i> Perfil</a>
                                            <a href="mensagens.php?contact=<?php echo intval($atleta['id']); ?>" class="btn-action"><i class="bi bi-chat-left-text"></i> Conversar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhum talento cadastrado ainda.</div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
