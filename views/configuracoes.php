<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/configuracoes.php
 * Descrição: Painel de configurações da conta, FAQ e Institucional.
 */
session_start();
require_once '../config/conexao.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$stmtUser = $pdo->prepare('SELECT nome, email FROM usuarios WHERE id = ?');
$stmtUser->execute([$userId]);
$userData = $stmtUser->fetch();
$userName = $userData['nome'] ?? $_SESSION['user_nome'] ?? 'Usuário';
$userEmail = $userData['email'] ?? $_SESSION['user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Configurações</title>
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

        .nav-pills .nav-link {
            color: var(--bs-body-color);
            font-weight: 500;
            padding: 1rem;
            border-radius: 10px;
            transition: all var(--transition-speed);
        }

        .nav-pills .nav-link.active {
            background-color: var(--fya-primary) !important;
            color: #000 !important;
            font-weight: 600;
        }

        .config-card {
            border: none;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            background-color: var(--bs-tertiary-bg);
        }

        .danger-zone {
            border: 2px solid #dc3545;
            background-color: rgba(220, 53, 69, 0.05);
        }

        .btn-fya {
            background-color: var(--fya-primary);
            color: #000;
            font-weight: 600;
            border: none;
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
            
            <h3 class="fw-bold mb-4">Configurações <span class="text-muted fs-6 fw-normal">/ Gerencie sua conta</span></h3>

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, rgba(154,205,50,0.16), rgba(255,255,255,0.03));">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-bold">Resumo da sua conta</div>
                        <div class="small text-muted">Acompanhe seu perfil, mantenha as informações atualizadas e continue recebendo mais oportunidades.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="editar_perfil.php" class="btn btn-sm btn-fya">Editar perfil</a>
                        <a href="buscar_atletas.php" class="btn btn-sm btn-outline-secondary">Explorar atletas</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Menu de Sub-abas -->
                <div class="col-lg-3 mb-4">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active text-start" id="pill-dados-tab" data-bs-toggle="pill" data-bs-target="#pill-dados" type="button" role="tab">
                            <i class="bi bi-person-gear me-2"></i> Dados da Conta
                        </button>
                        <button class="nav-link text-start" id="pill-ajuda-tab" data-bs-toggle="pill" data-bs-target="#pill-ajuda" type="button" role="tab">
                            <i class="bi bi-question-circle me-2"></i> Central de Ajuda
                        </button>
                        <button class="nav-link text-start" id="pill-sobre-tab" data-bs-toggle="pill" data-bs-target="#pill-sobre" type="button" role="tab">
                            <i class="bi bi-info-circle me-2"></i> Sobre Nós
                        </button>
                        <button class="nav-link text-start text-danger" id="pill-perigo-tab" data-bs-toggle="pill" data-bs-target="#pill-perigo" type="button" role="tab">
                            <i class="bi bi-exclamation-triangle me-2"></i> Zona de Perigo
                        </button>
                    </div>
                </div>

                <?php if (!empty($_GET['msg'])): ?>
                    <div class="alert alert-info mt-3"><?php echo htmlspecialchars($_GET['msg']); ?></div>
                <?php endif; ?>

                <!-- Conteúdo das Abas -->
                <div class="col-lg-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        
                        <!-- Aba: Dados da Conta -->
                        <div class="tab-pane fade show active" id="pill-dados" role="tabpanel">
                            <div class="config-card">
                                <h5 class="fw-bold mb-4">Alterar Informações Cadastrais</h5>
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="editar_perfil.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-pencil-square me-2"></i> Ir para editar perfil
                                    </a>
                                </div>
                                <form action="../controllers/user_controller.php?action=update" method="POST">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nome Completo</label>
                                            <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($userName); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">E-mail</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nova Senha</label>
                                            <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para não alterar">
                                        </div>
                                        <div class="col-md-6 d-flex align-items-end">
                                            <button type="submit" class="btn btn-fya px-4 py-2 w-100">Salvar Alterações</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Aba: Central de Ajuda -->
                        <div class="tab-pane fade" id="pill-ajuda" role="tabpanel">
                            <div class="config-card">
                                <h5 class="fw-bold mb-4">Perguntas Frequentes (FAQ)</h5>
                                <div class="accordion accordion-flush" id="faqAccordion">
                                    <div class="accordion-item border mb-2 rounded">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                                Como funciona a nota de avaliação?
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body text-muted small">
                                                A nota é calculada com base na média dos atributos técnicos e nas avaliações feitas por olheiros e clubes que visualizaram seu perfil.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item border mb-2 rounded">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                                Quanto tempo leva para um clube me responder?
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body text-muted small">
                                                O tempo varia conforme a demanda do clube, mas geralmente as notificações de candidatura são atualizadas em até 15 dias úteis.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item border mb-2 rounded">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                                Posso alterar meu tipo de conta?
                                            </button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body text-muted small">
                                                Por questões de segurança e integridade dos dados, a alteração de tipo de conta deve ser solicitada ao suporte administrativo.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aba: Sobre Nós -->
                        <div class="tab-pane fade" id="pill-sobre" role="tabpanel">
                            <div class="config-card">
                                <h5 class="fw-bold mb-4">Sobre a Plataforma FYA</h5>
                                <p class="text-muted leading-relaxed">
                                    O <strong>Find Your Athlete (FYA)</strong> nasceu da necessidade de democratizar o acesso ao esporte profissional no Brasil. Sabemos que milhares de talentos incríveis ficam escondidos em categorias de base de pequenas cidades por falta de visibilidade.
                                </p>
                                <p class="text-muted leading-relaxed">
                                    Nossa missão é criar a maior vitrine digital de talentos do país, conectando atletas sonhadores a avaliadores e clubes profissionais de forma justa, transparente e eficiente.
                                </p>
                                <div class="alert alert-success border-0 bg-success text-white mt-4 py-3">
                                    <i class="bi bi-heart-fill me-2"></i> Acreditamos que o talento não tem CEP.
                                </div>
                            </div>
                        </div>

                        <!-- Aba: Zona de Perigo -->
                        <div class="tab-pane fade" id="pill-perigo" role="tabpanel">
                            <div class="config-card danger-zone">
                                <h5 class="fw-bold mb-4 text-danger">Zona de Perigo</h5>
                                <p class="text-muted small mb-4">
                                    Cuidado! Esta ação é irreversível. Ao excluir sua conta, todos os seus dados, histórico de candidaturas e mensagens serão apagados permanentemente do nosso banco de dados.
                                </p>
                                <button class="btn btn-danger px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalDelete">
                                    <i class="bi bi-trash me-2"></i> Excluir Minha Conta
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </main>

        <!-- MODAL: Confirmação de Exclusão -->
        <div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold text-danger">Tem certeza absoluta?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="bi bi-exclamation-octagon text-danger" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">Você perderá todo o seu portfólio e conexões com clubes. Não há como recuperar esses dados.</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger px-4">Sim, Excluir Tudo</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
