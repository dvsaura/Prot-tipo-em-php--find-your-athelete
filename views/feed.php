<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/feed.php
 * Descrição: Painel Inicial com Feed de Atletas e Destaques.
 */
session_start();
require_once '../config/conexao.php';

// Proteção simples: Se não houver sessão, volta para o login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $stmtFeed = $pdo->prepare(
        "SELECT p.*, u.nome AS autor, u.id AS autor_id, a.foto_perfil, a.modalidade, a.cidade, a.estado, a.pais " .
        "LEFT JOIN atletas_perfil a ON a.id_usuario = u.id " .
        "ORDER BY p.data_criacao DESC LIMIT 8"
    );
    $stmtFeed->execute();
    $publicacoesFeed = $stmtFeed->fetchAll();
} catch (PDOException $e) {
    $publicacoesFeed = [];
}

try {
    $stmtAtletas = $pdo->prepare(
        "SELECT u.id, u.nome, u.tipo_conta, a.posicao, a.idade, a.velocidade, a.tecnica, a.fisico, a.visao_jogo, a.foto_perfil " .
        "FROM usuarios u " .
        "LEFT JOIN atletas_perfil a ON a.id_usuario = u.id " .
        "WHERE u.id != ? AND u.tipo_conta = 'atleta' " .
        "ORDER BY u.data_criacao DESC LIMIT 8"
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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --fya-primary: #9ACD32;
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
            transition: background-color var(--transition-speed);
        }

        /* Estilização dos Cards de Destaque (Horizontal) */
        .featured-card {
            background-color: var(--bs-tertiary-bg);
            border: none;
            border-radius: 15px;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            cursor: pointer;
            overflow: hidden;
        }

        .featured-card:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .featured-badge {
            background-color: var(--fya-primary);
            color: #000;
            font-weight: bold;
            font-size: 0.75rem;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        /* Estilização das "Figurinhas" de Atletas (Grid) */
        .athlete-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background-color: var(--bs-tertiary-bg);
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 100%;
        }

        .athlete-card:hover {
            transform: scale(1.02);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }

        .athlete-img-container {
            height: 220px;
            overflow: hidden;
            position: relative;
        }

        .athlete-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .athlete-info {
            padding: 1.2rem;
        }

        .athlete-name {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 1.1rem;
            margin-bottom: 0.2rem;
            color: var(--bs-body-color);
        }

        .athlete-meta {
            font-size: 0.85rem;
            color: var(--bs-secondary-color);
            margin-bottom: 1rem;
        }

        .metric-badge {
            background-color: rgba(154, 205, 50, 0.2);
            color: var(--fya-primary);
            font-weight: 600;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 5px;
            margin-right: 5px;
        }

        .card-actions {
            border-top: 1px solid var(--bs-border-color);
            display: flex;
            justify-content: space-around;
            padding: 0.8rem;
        }

        .btn-action {
            color: var(--bs-secondary-color);
            transition: color var(--transition-speed);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-action:hover {
            color: var(--fya-primary);
        }

        /* Ajuste do Layout com Sidebar */
        #main-content {
            padding-top: 70px; /* Espaço para o header */
        }
    </style>
</head>
<body>

    <!-- Inclui o Menu Lateral -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Conteúdo Principal -->
    <div id="main-content">
        
        <!-- Inclui a Barra Superior -->
        <?php include 'includes/header.php'; ?>

        <main class="container-fluid p-4">

            <!-- AÇÃO: Botão para abrir tela de criação de publicação -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="mb-4 d-flex justify-content-end">
                    <a href="nova_publicacao.php" class="btn btn-fya btn-lg">Adicionar publicação</a>
                </div>
            <?php endif; ?>

            <!-- SEÇÃO: Publicações Recentes -->
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h3 class="fw-bold m-0">Publicações Recentes <span class="text-muted fs-6 fw-normal">/ Atualizações dos atletas</span></h3>
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
                                        <div class="athlete-img-container bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 200px;">
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
                                            <img src="<?php echo $authorImg ?: 'https://ui-avatars.com/api/?name='.urlencode($post['autor']).'&background=9ACD32&color=fff'; ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="Autor">
                                            <div>
                                                <small class="d-block fw-semibold"><?php echo htmlspecialchars($post['autor']); ?></small>
                                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($post['data_criacao'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mb-3 small text-muted">
                                            <?php if (!empty($post['modalidade'])): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success"><?php echo htmlspecialchars($post['modalidade']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($post['cidade']) || !empty($post['estado']) || !empty($post['pais'])): ?>
                                                <span><?php echo htmlspecialchars(trim(($post['cidade'] ?: '') . ($post['cidade'] && $post['estado'] ? ', ' : '') . ($post['estado'] ?: '') . ($post['pais'] ? ' - ' : '') . ($post['pais'] ?: ''))); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-actions justify-content-between">
                                            <div>
                                                <a href="mensagens.php?contact=<?php echo intval($post['autor_id']); ?>" class="btn-action"><i class="bi bi-chat-left-text"></i> Conversar</a>
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
                                                    } catch (PDOException $e) {
                                                        $likesCount = 0;
                                                        $likedByUser = false;
                                                    }
                                                ?>
                                                <a href="../controllers/post_controller.php?action=like&id=<?php echo intval($post['id']); ?>" class="btn-action" title="Curtir">
                                                    <i class="bi <?php echo $likedByUser ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                </a>
                                                <small class="text-muted"><?php echo $likesCount; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhuma publicação foi encontrada no feed ainda. Publique algo no seu perfil para começar.</div>
                <?php endif; ?>
            </section>

            <!-- SEÇÃO: Novos Talentos (Grid de Atletas) -->
            <section>
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h3 class="fw-bold m-0">Novos Talentos <span class="text-muted fs-6 fw-normal">/ Cadastros recentes</span></h3>
                </div>

                <?php if (!empty($atletasFeed)): ?>
                    <div class="row g-4">
                        <?php foreach ($atletasFeed as $atleta): ?>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="card athlete-card h-100">
                                    <div class="athlete-img-container">
                                        <img src="<?php echo !empty($atleta['foto_perfil']) ? '../uploads/'.htmlspecialchars($atleta['foto_perfil']) : 'https://ui-avatars.com/api/?name='.urlencode($atleta['nome']).'&background=9ACD32&color=fff'; ?>" alt="<?php echo htmlspecialchars($atleta['nome']); ?>">
                                    </div>
                                    <div class="athlete-info">
                                        <div class="athlete-name"><?php echo htmlspecialchars($atleta['nome']); ?></div>
                                        <div class="athlete-meta"><?php echo htmlspecialchars($atleta['posicao'] ?: 'Atleta'); ?> • <?php echo htmlspecialchars($atleta['idade'] ?: '--'); ?> anos</div>
                                        <div class="d-flex flex-wrap gap-1 mb-3">
                                            <span class="metric-badge">VEL <?php echo intval($atleta['velocidade']); ?></span>
                                            <span class="metric-badge">TEC <?php echo intval($atleta['tecnica']); ?></span>
                                            <span class="metric-badge">VIS <?php echo intval($atleta['visao_jogo']); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-actions">
                                        <a href="mensagens.php?contact=<?php echo intval($atleta['id']); ?>" class="btn-action"><i class="bi bi-chat-left-text"></i> Conversar</a>
                                        <a href="buscar_atletas.php" class="btn-action"><i class="bi bi-eye"></i> Perfil</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Ainda não há atletas cadastrados para exibir. Convide seus colegas e comece a trocar mensagens.</div>
                <?php endif; ?>
            </section>

        </main>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
