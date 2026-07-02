<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/perfil_atleta.php
 * Descrição: Perfil completo do atleta estilo portfólio/Instagram.
 */
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Busca dados do usuário e do perfil do atleta em um único JOIN
    $stmt = $pdo->prepare("
        SELECT u.nome, u.email, p.* 
        FROM usuarios u 
        JOIN atletas_perfil p ON u.id = p.id_usuario 
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $perfil = $stmt->fetch();

    // Se o perfil não existir (ex: acabou de se cadastrar), cria um registro básico
    if (!$perfil) {
        $stmt = $pdo->prepare("INSERT INTO atletas_perfil (id_usuario) VALUES (?)");
        $stmt->execute([$userId]);
        header("Refresh:0");
        exit();
    }
} catch (PDOException $e) {
    die("Erro ao carregar perfil: " . $e->getMessage());
}

// Busca publicações do atleta
try {
    $stmtPosts = $pdo->prepare("SELECT * FROM publicacoes WHERE id_usuario = ? ORDER BY data_criacao DESC");
    $stmtPosts->execute([$userId]);
    $publicacoes = $stmtPosts->fetchAll();
} catch (PDOException $e) {
    $publicacoes = [];
}

$alertMessage = '';
if (!empty($_GET['msg'])) {
    $alertMessage = htmlspecialchars($_GET['msg']);
}

// Define foto padrão caso não tenha imagem
$foto = !empty($perfil['foto_perfil']) ? '../uploads/' . $perfil['foto_perfil'] : 'https://i.pravatar.cc/300?u=' . $userId;
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Meu Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --fya-primary: #9ACD32;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Cabeçalho do Perfil */
        .profile-header {
            position: relative;
            margin-bottom: 5rem;
        }

        .profile-cover {
            height: 200px;
            width: 100%;
            background: url('https://images.unsplash.com/photo-1508098682722-e99c4372d7a6?q=80&w=1000') center/cover no-repeat;
            border-radius: 0 0 20px 20px;
        }

        .profile-avatar-container {
            position: absolute;
            top: 120px;
            left: 30px;
            display: flex;
            align-items: center;
        }

        .profile-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 5px solid var(--bs-body-bg);
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .verified-badge {
            position: absolute;
            top: 180px;
            left: 150px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .profile-rating {
            position: absolute;
            top: 130px;
            right: 30px;
            background-color: var(--bs-body-bg);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
        }

        /* Seções de Conteúdo */
        .section-card {
            background-color: var(--bs-tertiary-bg);
            border: none;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .attr-label {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: block;
        }

        .social-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--bs-body-color);
            transition: all var(--transition-speed);
            text-decoration: none;
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
        }

        .social-link:hover {
            background-color: var(--fya-primary);
            color: #000;
            transform: translateY(-3px);
        }

        #main-content {
            padding-top: 70px;
        }

        @media (max-width: 768px) {
            .profile-cover { height: 150px; }
            .profile-avatar-container { top: 80px; left: 50%; transform: translateX(-50%); }
            .profile-avatar { width: 100px; height: 100px; }
            .verified-badge { top: 140px; left: calc(50% + 60px); }
            .profile-rating { top: 90px; right: 20px; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main class="container-fluid p-4">
            <?php if (!empty($alertMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $alertMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- CABEÇALHO DO PERFIL -->
            <section class="profile-header">
                <div class="profile-cover"></div>
                <div class="profile-avatar-container">
                    <img src="<?php echo $foto; ?>" class="profile-avatar" alt="Avatar do Atleta">
                </div>
                <div class="verified-badge">
                    <i class="bi bi-check-verified"></i>
                </div>
                <div class="profile-rating">
                    <i class="bi bi-star-fill text-warning"></i> <?php echo number_format($perfil['nota_media'], 1); ?>
                </div>
            </section>

            <div class="row mt-5 g-4">
                <!-- Coluna Esquerda: Bio e Info -->
                <div class="col-lg-4">
                    <div class="section-card text-center text-lg-start">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h4 class="fw-bold m-0"><?php echo htmlspecialchars($perfil['nome']); ?></h4>
                            <a href="editar_perfil.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Editar</a>
                        </div>
                        
                        <div class="mb-3 text-lg-start">
                            <span class="text-muted small d-block">E-mail</span>
                            <span class="fw-semibold"><?php echo htmlspecialchars($perfil['email']); ?></span>
                        </div>
                        <div class="row g-3 mb-3 text-lg-start">
                            <div class="col-4">
                                <span class="text-muted small d-block">Idade</span>
                                <span class="fw-semibold"><?php echo $perfil['idade'] ?? '---'; ?> anos</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small d-block">Peso</span>
                                <span class="fw-semibold"><?php echo $perfil['peso'] ?? '---'; ?> kg</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small d-block">Altura</span>
                                <span class="fw-semibold"><?php echo $perfil['altura'] ?? '---'; ?> m</span>
                            </div>
                        </div>
                        <hr>
                        <h5 class="fw-bold mb-3 text-lg-start">Sobre</h5>
                        <p class="text-muted small text-lg-start">
                            <?php echo !empty($perfil['bio']) ? nl2br(htmlspecialchars($perfil['bio'])) : 'Nenhuma bio adicionada ainda. Edite seu perfil!'; ?>
                        </p>
                        <div class="d-flex justify-content-center justify-content-lg-start gap-2 mt-4">
                            <a href="<?php echo $perfil['youtube_link']; ?>" target="_blank" class="social-link" title="YouTube"><i class="bi bi-youtube"></i></a>
                            <a href="<?php echo $perfil['tiktok_link']; ?>" target="_blank" class="social-link" title="TikTok"><i class="bi bi-tiktok"></i></a>
                            <a href="<?php echo $perfil['instagram_link']; ?>" target="_blank" class="social-link" title="Instagram"><i class="bi bi-instagram"></i></a>
                            <a href="<?php echo $perfil['curriculo_link']; ?>" target="_blank" class="social-link" title="Currículo"><i class="bi bi-file-earmark-pdf"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita: Desempenho Técnico -->
                <div class="col-lg-8">
                    <div class="section-card">
                        <h4 class="fw-bold mb-4">Análise de Desempenho Técnico</h4>

                        <div class="mb-4">
                            <span class="attr-label">Velocidade <span class="float-end text-muted"><?php echo $perfil['velocidade']; ?>%</span></span>
                            <div class="progress" style="height: 12px; border-radius: 10px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $perfil['velocidade']; ?>%; background-color: var(--fya-primary);"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <span class="attr-label">Técnica <span class="float-end text-muted"><?php echo $perfil['tecnica']; ?>%</span></span>
                            <div class="progress" style="height: 12px; border-radius: 10px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $perfil['tecnica']; ?>%; background-color: var(--fya-primary);"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <span class="attr-label">Físico <span class="float-end text-muted"><?php echo $perfil['fisico']; ?>%</span></span>
                            <div class="progress" style="height: 12px; border-radius: 10px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $perfil['fisico']; ?>%; background-color: var(--fya-primary);"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <span class="attr-label">Visão de Jogo <span class="float-end text-muted"><?php echo $perfil['visao_jogo']; ?>%</span></span>
                            <div class="progress" style="height: 12px; border-radius: 10px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $perfil['visao_jogo']; ?>%; background-color: var(--fya-primary);"></div>
                            </div>
                        </div>

                        <div class="mt-5 p-4 bg-body rounded-3 border">
                            <h6 class="fw-bold mb-3"><i class="bi bi-trophy text-warning me-2"></i> Histórico de Campeonatos</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover small">
                                    <thead>
                                        <tr>
                                            <th>Ano</th>
                                            <th>Competição</th>
                                            <th>Posição</th>
                                            <th>Gols/Assist</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Aqui entrariam os dados reais de conquistas futuramente -->
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Nenhum campeonato registrado.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-5 section-card">
                            <h4 class="fw-bold mb-4">Publicações Recentes</h4>
                            <?php if (!empty($publicacoes)): ?>
                                <div class="row g-4">
                                    <?php foreach ($publicacoes as $post): ?>
                                        <div class="col-md-6">
                                            <div class="card h-100 shadow-sm">
                                                <?php if (!empty($post['imagem'])): ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($post['imagem']); ?>" class="card-img-top" alt="Publicação de <?php echo htmlspecialchars($perfil['nome']); ?>" style="height: 240px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($post['titulo'] ?: 'Sem título'); ?></h5>
                                                        <a href="../controllers/post_controller.php?action=delete&id=<?php echo intval($post['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja realmente excluir esta publicação?');">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                    <p class="card-text text-muted"><?php echo nl2br(htmlspecialchars($post['descricao'] ?: 'Publicação sem descrição.')); ?></p>
                                                </div>
                                                <div class="card-footer bg-body py-3 text-muted small d-flex justify-content-between align-items-center">
                                                    <span>Publicado em <?php echo date('d/m/Y', strtotime($post['data_criacao'])); ?></span>
                                                    <span class="badge bg-success">Minha publicação</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Nenhuma publicação com imagem encontrada. Faça um post no seu perfil para exibir aqui.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
