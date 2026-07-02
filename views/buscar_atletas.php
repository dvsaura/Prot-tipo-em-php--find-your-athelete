<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/buscar_atletas.php
 * Descrição: Mecanismo de busca e filtragem de atletas.
 */
session_start();
require_once '../config/conexao.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Parâmetros de busca
$q = trim($_GET['q'] ?? '');
$posicaoFilter = $_GET['posicao'] ?? '';
$generoFilter = $_GET['genero'] ?? '';
$faixaFilter = $_GET['faixa'] ?? '';
$sort = $_GET['sort'] ?? 'recent';

// Monta a query dinamicamente
$sql = "SELECT u.id, u.nome, a.posicao, a.idade, a.velocidade, a.tecnica, a.fisico, a.visao_jogo, a.foto_perfil, u.data_criacao " .
       "FROM usuarios u LEFT JOIN atletas_perfil a ON a.id_usuario = u.id WHERE u.tipo_conta = 'atleta'";
$params = [];

if ($q !== '') {
    $sql .= " AND (u.nome LIKE ? OR a.posicao LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if (!empty($posicaoFilter) && $posicaoFilter !== 'all') {
    $sql .= " AND a.posicao = ?";
    $params[] = $posicaoFilter;
}

if (!empty($faixaFilter) && strpos($faixaFilter, '-') !== false) {
    [$minAge, $maxAge] = explode('-', $faixaFilter);
    $sql .= " AND a.idade BETWEEN ? AND ?";
    $params[] = intval($minAge);
    $params[] = intval($maxAge);
}

if ($sort === 'melhores') {
    $sql .= " ORDER BY (COALESCE(a.velocidade,0) + COALESCE(a.tecnica,0)) DESC";
} else {
    $sql .= " ORDER BY u.data_criacao DESC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    $results = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Buscar Atletas</title>
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
            background-color: var(--bs-body-bg);
        }

        /* Barra de Pesquisa Estilizada */
        .search-container {
            background-color: var(--bs-tertiary-bg);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .search-input-group {
            background-color: var(--bs-body-bg);
            border-radius: 50px;
            padding: 5px 5px 5px 20px;
            border: 2px solid transparent;
            transition: border-color var(--transition-speed);
        }

        .search-input-group:focus-within {
            border-color: var(--fya-primary);
        }

        .search-input-group input {
            border: none;
            background: transparent;
            box-shadow: none;
        }

        /* Chips/Pills de Filtragem */
        .filter-pills .btn-pill {
            border-radius: 50px;
            padding: 5px 15px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            transition: all var(--transition-speed);
            margin: 5px;
        }

        .filter-pills .btn-pill.active, .filter-pills .btn-pill:hover {
            background-color: var(--fya-primary) !important;
            color: #000 !important;
            border-color: var(--fya-primary);
        }

        /* Estilo Figurinha (Consistente com o Feed) */
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
            color: var(--bs-body-color);
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

        #main-content {
            padding-top: 70px;
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main class="container-fluid p-4">
            
            <h3 class="fw-bold mb-4">Busca Inteligente <span class="text-muted fs-6 fw-normal">/ Filtre os melhores talentos</span></h3>

            <!-- Área de Busca e Filtros -->
            <section class="search-container">
                <form method="GET" action="buscar_atletas.php">
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-12">
                            <div class="search-input-group d-flex align-items-center">
                                <i class="bi bi-search me-2 text-muted"></i>
                                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Pesquisar por nome, posição ou cidade...">
                                <button type="submit" class="btn btn-fya rounded-circle p-2 px-3 ms-2" style="background-color: var(--fya-primary); color: #000;">Buscar</button>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 d-flex align-items-center justify-content-lg-end">
                            <div class="filter-pills d-flex flex-wrap">
                                <span class="text-muted me-2 small fw-bold align-self-center">Filtros:</span>
                                <select name="posicao" class="form-select form-select-sm w-auto me-2">
                                    <option value="all">Todas posições</option>
                                    <option value="Atacante" <?php echo ($posicaoFilter==='Atacante')? 'selected':''; ?>>Atacante</option>
                                    <option value="Meio-Campo" <?php echo ($posicaoFilter==='Meio-Campo')? 'selected':''; ?>>Meio-Campo</option>
                                    <option value="Zagueiro" <?php echo ($posicaoFilter==='Zagueiro')? 'selected':''; ?>>Zagueiro</option>
                                    <option value="Goleiro" <?php echo ($posicaoFilter==='Goleiro')? 'selected':''; ?>>Goleiro</option>
                                </select>
                                <select name="faixa" class="form-select form-select-sm w-auto me-2">
                                    <option value="">Faixa Etária</option>
                                    <option value="14-16" <?php echo ($faixaFilter==='14-16')? 'selected':''; ?>>14-16 anos</option>
                                    <option value="17-19" <?php echo ($faixaFilter==='17-19')? 'selected':''; ?>>17-19 anos</option>
                                    <option value="20-99" <?php echo ($faixaFilter==='20-99')? 'selected':''; ?>>20+ anos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Grid de Resultados -->
            <section>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="text-muted mb-0">Encontramos <strong>12 atletas</strong> correspondentes.</p>
                    <select class="form-select form-select-sm w-auto">
                        <option>Mais Recentes</option>
                        <option>Melhor Avaliados</option>
                        <option>Mais Jovens</option>
                    </select>
                </div>

                <div class="row g-4">
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $athlete): ?>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="card athlete-card">
                                    <div class="athlete-img-container">
                                        <?php
                                            $imgPath = __DIR__ . '/../uploads/' . ($athlete['foto_perfil'] ?? '');
                                            if (!empty($athlete['foto_perfil']) && file_exists($imgPath)) {
                                                $imgSrc = '../uploads/' . htmlspecialchars($athlete['foto_perfil']);
                                            } else {
                                                $imgSrc = 'https://ui-avatars.com/api/?name=' . urlencode($athlete['nome']) . '&background=9ACD32&color=fff';
                                            }
                                        ?>
                                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($athlete['nome']); ?>">
                                    </div>
                                    <div class="athlete-info">
                                        <div class="athlete-name"><?php echo htmlspecialchars($athlete['nome']); ?></div>
                                        <div class="text-muted small mb-2"><?php echo htmlspecialchars($athlete['posicao'] ?: 'Atleta'); ?> • <?php echo htmlspecialchars($athlete['idade'] ?: '--'); ?> anos</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="metric-badge">VEL <?php echo intval($athlete['velocidade']); ?></span>
                                            <span class="metric-badge">TEC <?php echo intval($athlete['tecnica']); ?></span>
                                            <span class="metric-badge">VIS <?php echo intval($athlete['visao_jogo']); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-actions d-flex justify-content-around p-3 border-top">
                                        <a href="mensagens.php?contact=<?php echo intval($athlete['id']); ?>" class="btn-action text-muted"><i class="bi bi-chat-left-text"></i></a>
                                        <a href="perfil_atleta.php?id=<?php echo intval($athlete['id']); ?>" class="btn-action text-muted"><i class="bi bi-eye"></i> Perfil</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">Nenhum atleta correspondente foi encontrado.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
