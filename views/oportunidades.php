<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/oportunidades.php
 * Descrição: Gestão de vagas e peneiras com controle de acesso por tipo de conta.
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$tipo_conta = $_SESSION['user_tipo'] ?? 'atleta';
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Oportunidades</title>
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

        .opp-card {
            border: none;
            border-radius: 15px;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-left: 5px solid var(--fya-primary);
        }

        .opp-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .btn-fya {
            background-color: var(--fya-primary);
            color: #000;
            font-weight: 600;
            border: none;
            transition: transform var(--transition-speed);
        }

        .btn-fya:hover {
            transform: scale(1.05);
            background-color: #8ab52b;
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
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold m-0">Oportunidades & Vagas <span class="text-muted fs-6 fw-normal">/ Encontre seu lugar no esporte</span></h3>
                </div>

                <!-- CONTROLE DE ACESSO: Apenas Avaliadores veem o botão de publicar -->
                <?php if ($tipo_conta === 'avaliador'): ?>
                    <button class="btn btn-fya px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalPublicar">
                        <i class="bi bi-plus-circle me-2"></i> Publicar Oportunidade
                    </button>
                <?php endif; ?>
            </div>

            <!-- Grid de Oportunidades -->
            <div class="row g-4">
                <!-- Vaga Mock 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card opp-card p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-secondary mb-2">Futebol</span>
                            <span class="text-muted small"><i class="bi bi-calendar3"></i> Prazo: 15 Out</span>
                        </div>
                        <h5 class="fw-bold">Peneira Sub-17 - Clube Atlético Mineiro</h5>
                        <p class="text-muted small mb-3">Busca-se laterais e meias com boa visão de jogo e vigor físico.</p>
                        
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-2 bg-body-tertiary rounded text-center">
                                    <small class="d-block text-muted">Idade</small>
                                    <strong>16 - 17 anos</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-body-tertiary rounded text-center">
                                    <small class="d-block text-muted">Pé Dominante</small>
                                    <strong>Ambos</strong>
                                </div>
                            </div>
                        </div>

                        <?php if ($tipo_conta === 'atleta'): ?>
                            <button class="btn btn-fya w-100" onclick="alert('Sua candidatura foi enviada com sucesso!')">
                                <i class="bi bi-send me-2"></i> Candidatar-se Agora
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary w-100">Gerenciar Candidaturas</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Vaga Mock 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card opp-card p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-secondary mb-2">Basquete</span>
                            <span class="text-muted small"><i class="bi bi-calendar3"></i> Prazo: 20 Out</span>
                        </div>
                        <h5 class="fw-bold">Vaga de Base - Academia Esportiva SP</h5>
                        <p class="text-muted small mb-3">Vaga para pivôs com altura mínima de 1.95m e experiência em campeonatos estaduais.</p>
                        
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-2 bg-body-tertiary rounded text-center">
                                    <small class="d-block text-muted">Idade</small>
                                    <strong>15 - 18 anos</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-body-tertiary rounded text-center">
                                    <small class="d-block text-muted">Peso Mín.</small>
                                    <strong>75 kg</strong>
                                </div>
                            </div>
                        </div>

                        <?php if ($tipo_conta === 'atleta'): ?>
                            <button class="btn btn-fya w-100" onclick="alert('Sua candidatura foi enviada com sucesso!')">
                                <i class="bi bi-send me-2"></i> Candidatar-se Agora
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary w-100">Gerenciar Candidaturas</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </main>

        <!-- MODAL: Publicar Oportunidade (Apenas para Avaliadores) -->
        <?php if ($tipo_conta === 'avaliador'): ?>
        <div class="modal fade" id="modalPublicar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Publicar Nova Oportunidade</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="../controllers/opp_controller.php?action=create" method="POST">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Título da Vaga</label>
                                    <input type="text" name="titulo" class="form-control" placeholder="Ex: Peneira Sub-20 - Centroalfa" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Categoria/Esporte</label>
                                    <input type="text" name="categoria" class="form-control" placeholder="Ex: Futebol" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Requisitos Técnicos</label>
                                    <textarea name="requisitos" class="form-control" rows="3" placeholder="Descreva o que você busca no atleta..."></textarea>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Idade Mínima</label>
                                    <input type="number" name="idade_min" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Idade Máxima</label>
                                    <input type="number" name="idade_max" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Peso Mínimo (kg)</label>
                                    <input type="number" step="0.1" name="peso_min" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Pé Dominante</label>
                                    <select name="pe_dominante" class="form-select">
                                        <option value="direito">Direito</option>
                                        <option value="esquerdo">Esquerdo</option>
                                        <option value="ambos">Ambos</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Data Limite de Inscrição</label>
                                    <input type="date" name="data_limite" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-fya px-4">Publicar Agora</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
