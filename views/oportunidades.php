<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/oportunidades.php
 * Descrição: Gestão de vagas e peneiras com controle de acesso por tipo de conta.
 */
session_start();
require_once '../config/conexao.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$tipo_conta = $_SESSION['user_tipo'] ?? 'atleta';
$userId = (int)($_SESSION['user_id'] ?? 0);
$successMessage = $_GET['msg'] ?? '';

try {
    $stmtOpps = $pdo->prepare('SELECT * FROM oportunidades ORDER BY data_criacao DESC');
    $stmtOpps->execute();
    $oportunidades = $stmtOpps->fetchAll();
} catch (PDOException $e) {
    $oportunidades = [];
}

$manageId = intval($_GET['manage_id'] ?? 0);
$manageCandidaturas = [];
$canManageOpportunity = false;
if ($manageId > 0 && $tipo_conta === 'avaliador') {
    try {
        $stmtOwner = $pdo->prepare('SELECT id FROM oportunidades WHERE id = ? AND id_usuario_avaliador = ?');
        $stmtOwner->execute([$manageId, $userId]);
        $canManageOpportunity = (bool)$stmtOwner->fetch();

        if ($canManageOpportunity) {
            $stmtCand = $pdo->prepare(
                'SELECT c.id, c.status, c.data_candidatura, u.nome, u.email FROM candidaturas c JOIN usuarios u ON u.id = c.id_usuario_atleta WHERE c.id_oportunidade = ? ORDER BY c.data_candidatura DESC'
            );
            $stmtCand->execute([$manageId]);
            $manageCandidaturas = $stmtCand->fetchAll();
        }
    } catch (PDOException $e) {
        $manageCandidaturas = [];
        $canManageOpportunity = false;
    }
}
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
            display: flex;
            flex-direction: column;
            height: 100%;
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

        .opp-card .btn {
            margin-top: auto;
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
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold m-0">Oportunidades & Vagas <span class="text-muted fs-6 fw-normal">/ Encontre seu lugar no esporte</span></h3>
                </div>

                <!-- CONTROLE DE ACESSO: Apenas Avaliadores veem o botão de publicar -->
                <?php if ($tipo_conta === 'avaliador'): ?>
                    <button class="btn btn-fya px-4 py-2 d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modalPublicar">
                        <i class="bi bi-plus-circle me-2"></i> Publicar Oportunidade
                    </button>
                <?php endif; ?>
            </div>

            <!-- Grid de Oportunidades -->
            <div class="row g-4">
                <?php if (!empty($oportunidades)): ?>
                    <?php foreach ($oportunidades as $oportunidade): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card opp-card p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($oportunidade['categoria'] ?: 'Geral'); ?></span>
                                    <span class="text-muted small"><i class="bi bi-calendar3"></i> Prazo: <?php echo htmlspecialchars($oportunidade['data_limite'] ?: '--'); ?></span>
                                </div>
                                <h5 class="fw-bold"><?php echo htmlspecialchars($oportunidade['titulo']); ?></h5>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($oportunidade['requisitos'] ?: 'Descrição disponível em breve.'); ?></p>
                                
                                <div class="row g-2 mb-4">
                                    <div class="col-6">
                                        <div class="p-2 bg-body-tertiary rounded text-center">
                                            <small class="d-block text-muted">Idade</small>
                                            <strong><?php echo htmlspecialchars(($oportunidade['idade_min'] ?: '?') . ' - ' . ($oportunidade['idade_max'] ?: '?')); ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 bg-body-tertiary rounded text-center">
                                            <small class="d-block text-muted">Pé Dominante</small>
                                            <strong><?php echo htmlspecialchars(ucfirst($oportunidade['pe_dominante_pref'] ?: 'ambos')); ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($tipo_conta === 'atleta'): ?>
                                    <a class="btn btn-fya w-100 d-flex align-items-center justify-content-center" href="../controllers/opp_controller.php?action=apply&id=<?php echo intval($oportunidade['id']); ?>">
                                        <i class="bi bi-send me-2"></i> Candidatar-se Agora
                                    </a>
                                <?php elseif ((int)$oportunidade['id_usuario_avaliador'] === $userId): ?>
                                    <a class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center" href="../controllers/opp_controller.php?action=manage&id=<?php echo intval($oportunidade['id']); ?>">Gerenciar Candidaturas</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-info">Nenhuma oportunidade cadastrada ainda.</div></div>
                <?php endif; ?>
            </div>

            <?php if ($tipo_conta === 'avaliador' && $manageId > 0): ?>
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Candidaturas recebidas</h5>
                        <?php if (!$canManageOpportunity): ?>
                            <div class="alert alert-warning mb-0">Você não pode visualizar candidaturas desta oportunidade.</div>
                        <?php elseif (!empty($manageCandidaturas)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Atleta</th>
                                            <th>E-mail</th>
                                            <th>Status</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($manageCandidaturas as $candidatura): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($candidatura['nome']); ?></td>
                                                <td><?php echo htmlspecialchars($candidatura['email']); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($candidatura['status'])); ?></td>
                                                <td>
                                                    <form action="../controllers/opp_controller.php?action=update_status" method="POST" class="d-flex gap-2">
                                                        <input type="hidden" name="opportunity_id" value="<?php echo intval($manageId); ?>">
                                                        <input type="hidden" name="candidature_id" value="<?php echo intval($candidatura['id']); ?>">
                                                        <select name="status" class="form-select form-select-sm w-auto">
                                                            <option value="pendente" <?php echo $candidatura['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                                            <option value="aceito" <?php echo $candidatura['status'] === 'aceito' ? 'selected' : ''; ?>>Aceito</option>
                                                            <option value="recusado" <?php echo $candidatura['status'] === 'recusado' ? 'selected' : ''; ?>>Recusado</option>
                                                        </select>
                                                        <button type="submit" class="btn btn-sm btn-fya">Salvar</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">Nenhuma candidatura para esta oportunidade ainda.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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
                                <div class="col-md-6">
                                    <label class="form-label">Peso Mínimo (kg)</label>
                                    <input type="number" step="0.1" name="peso_min" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Data Limite de Inscrição</label>
                                    <input type="date" name="data_limite" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 justify-content-end gap-2">
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
