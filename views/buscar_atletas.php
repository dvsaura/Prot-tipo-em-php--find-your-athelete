<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/buscar_atletas.php
 * Descrição: Mecanismo de busca e filtragem de atletas.
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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
                <div class="row g-3">
                    <div class="col-lg-6 col-md-12">
                        <div class="search-input-group d-flex align-items-center">
                            <i class="bi bi-search me-2 text-muted"></i>
                            <input type="text" class="form-control" placeholder="Pesquisar por nome, posição ou cidade...">
                            <button class="btn btn-fya rounded-circle p-2 px-3 ms-2" style="background-color: var(--fya-primary); color: #000;">Buscar</button>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 d-flex align-items-center justify-content-lg-end">
                        <div class="filter-pills d-flex flex-wrap">
                            <span class="text-muted me-2 small fw-bold align-self-center">Filtros:</span>
                            <button class="btn btn-pill active">Futebol</button>
                            <button class="btn btn-pill">Basquete</button>
                            <button class="btn btn-pill">Vôlei</button>
                            <button class="btn btn-pill">Handebol</button>
                        </div>
                    </div>
                </div>

                <div class="row mt-3 g-2">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm border-0 bg-body">
                            <option selected>Posição (Todas)</option>
                            <option>Atacante</option>
                            <option>Meio-Campo</option>
                            <option>Zagueiro</option>
                            <option>Goleiro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm border-0 bg-body">
                            <option selected>Gênero (Todos)</option>
                            <option>Masculino</option>
                            <option>Feminino</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm border-0 bg-body">
                            <option selected>Faixa Etária</option>
                            <option>14-16 anos</option>
                            <option>17-19 anos</option>
                            <option>20+ anos</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-sm btn-link text-muted text-decoration-none"><i class="bi bi-arrow-clockwise"></i> Limpar Filtros</button>
                    </div>
                </div>
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
                    <!-- Atleta Mock 1 -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card athlete-card">
                            <div class="athlete-img-container">
                                <img src="https://images.unsplash.com/photo-1508098682722-e99c4372d7a6?q=80&w=500" alt="Atleta">
                            </div>
                            <div class="athlete-info">
                                <div class="athlete-name">Ricardo Oliveira</div>
                                <div class="text-muted small mb-2">Centroavante • 17 anos</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="metric-badge">VEL 85</span>
                                    <span class="metric-badge">TEC 90</span>
                                    <span class="metric-badge">FIS 82</span>
                                </div>
                            </div>
                            <div class="card-actions d-flex justify-content-around p-3 border-top">
                                <a href="#" class="btn-action text-muted"><i class="bi bi-heart"></i></a>
                                <a href="perfil_atleta.php" class="btn-action text-muted"><i class="bi bi-eye"></i> Perfil</a>
                            </div>
                        </div>
                    </div>
                    <!-- Atleta Mock 2 -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card athlete-card">
                            <div class="athlete-img-container">
                                <img src="https://images.unsplash.com/photo-1543351611-58f695a9737d?q=80&w=500" alt="Atleta">
                            </div>
                            <div class="athlete-info">
                                <div class="athlete-name">Felipe Santos</div>
                                <div class="text-muted small mb-2">Volante • 16 anos</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="metric-badge">VEL 78</span>
                                    <span class="metric-badge">TEC 88</span>
                                    <span class="metric-badge">VIS 92</span>
                                </div>
                            </div>
                            <div class="card-actions d-flex justify-content-around p-3 border-top">
                                <a href="#" class="btn-action text-muted"><i class="bi bi-heart"></i></a>
                                <a href="perfil_atleta.php" class="btn-action text-muted"><i class="bi bi-eye"></i> Perfil</a>
                            </div>
                        </div>
                    </div>
                    <!-- Atleta Mock 3 -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card athlete-card">
                            <div class="athlete-img-container">
                                <img src="https://images.unsplash.com/photo-1552667466-765757bc977a?q=80&w=500" alt="Atleta">
                            </div>
                            <div class="athlete-info">
                                <div class="athlete-name">Bruno Mendes</div>
                                <div class="text-muted small mb-2">Lateral Dir • 17 anos</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="metric-badge">VEL 95</span>
                                    <span class="metric-badge">TEC 80</span>
                                    <span class="metric-badge">FIS 88</span>
                                </div>
                            </div>
                            <div class="card-actions d-flex justify-content-around p-3 border-top">
                                <a href="#" class="btn-action text-muted"><i class="bi bi-heart"></i></a>
                                <a href="perfil_atleta.php" class="btn-action text-muted"><i class="bi bi-eye"></i> Perfil</a>
                            </div>
                        </div>
                    </div>
                    <!-- Atleta Mock 4 -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card athlete-card">
                            <div class="athlete-img-container">
                                <img src="https://images.unsplash.com/photo-1574629810360-7ef95f699943?q=80&w=500" alt="Atleta">
                            </div>
                            <div class="athlete-info">
                                <div class="athlete-name">Tiago Rocha</div>
                                <div class="text-muted small mb-2">Meia • 18 anos</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="metric-badge">VEL 82</span>
                                    <span class="metric-badge">TEC 94</span>
                                    <span class="metric-badge">VIS 91</span>
                                </div>
                            </div>
                            <div class="card-actions d-flex justify-content-around p-3 border-top">
                                <a href="#" class="btn-action text-muted"><i class="bi bi-heart"></i></a>
                                <a href="perfil_atleta.php" class="btn-action text-muted"><i class="bi bi-eye"></i> Perfil</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
