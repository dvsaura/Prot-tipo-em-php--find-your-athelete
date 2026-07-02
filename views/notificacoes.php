<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/notificacoes.php
 * Descrição: Central de alertas e notificações do sistema.
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
    <title>FYA - Notificações</title>
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

        #main-content {
            padding-top: 70px;
        }

        /* Estilo da Lista de Notificações */
        .notif-item {
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all var(--transition-speed);
            background-color: var(--bs-body-bg);
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .notif-item:hover {
            background-color: var(--bs-tertiary-bg);
            transform: translateX(5px);
        }

        .notif-item.unread {
            border-left: 4px solid var(--fya-primary);
            background-color: rgba(154, 205, 50, 0.05);
        }

        .notif-dot {
            width: 10px;
            height: 10px;
            background-color: var(--fya-primary);
            border-radius: 50%;
            margin-right: 15px;
            display: inline-block;
        }

        .category-badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .bg-msg { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .bg-view { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-cand { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-sys { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }

    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main class="container-fluid p-4">
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold m-0">Central de Notificações <span class="text-muted fs-6 fw-normal">/ Fique por dentro de tudo</span></h3>
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="markAllRead">
                    <i class="bi bi-check2-all me-2"></i> Marcar todas como lidas
                </button>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    <!-- Categoria: Mensagens -->
                    <h6 class="text-muted fw-bold text-uppercase small mb-3"><i class="bi bi-chat-dots me-2"></i> Mensagens</h6>
                    <div class="notif-list mb-5">
                        <a href="mensagens.php" class="notif-item unread">
                            <div class="notif-dot"></div>
                            <div class="flex-grow-1">
                                <span class="category-badge bg-msg">Mensagem</span>
                                <div class="fw-semibold">Ricardo Oliveira enviou uma nova mensagem.</div>
                                <small class="text-muted">Há 5 minutos</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="mensagens.php" class="notif-item">
                            <div class="notif-dot" style="background-color: transparent;"></div>
                            <div class="flex-grow-1">
                                <span class="category-badge bg-msg">Mensagem</span>
                                <div class="fw-semibold">Clube Atlético SP respondeu sua dúvida.</div>
                                <small class="text-muted">Há 2 horas</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>

                    <!-- Categoria: Perfil -->
                    <h6 class="text-muted fw-bold text-uppercase small mb-3"><i class="bi bi-eye"></i> Visualizações de Perfil</h6>
                    <div class="notif-list mb-5">
                        <a href="perfil_atleta.php" class="notif-item unread">
                            <div class="notif-dot"></div>
                            <div class="flex-grow-1">
                                <span class="category-badge bg-view">Perfil</span>
                                <div class="fw-semibold">Um olheiro do Fluminense visualizou seu portfólio.</div>
                                <small class="text-muted">Há 1 hora</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="perfil_atleta.php" class="notif-item">
                            <div class="notif-dot" style="background-color: transparent;"></div>
                            <div class="flex-grow-1">
                                <span class="category-badge bg-view">Perfil</span>
                                <div class="fw-semibold">Seu perfil recebeu 15 visualizações nas últimas 24h.</div>
                                <small class="text-muted">Ontem</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>

                    <!-- Categoria: Candidaturas -->
                    <h6 class="text-muted fw-bold text-uppercase small mb-3"><i class="bi bi-file-earmark-check"></i> Status de Candidatura</h6>
                    <div class="notif-list mb-5">
                        <a href="oportunidades.php" class="notif-item unread">
                            <div class="notif-dot"></div>
                            <div class="flex-grow-1">
                                <span class="category-badge bg-cand">Candidatura</span>
                                <div class="fw-semibold">Sua candidatura para "Sub-17 Atlético MG" foi ACEITA!</div>
                                <small class="text-muted">Há 3 horas</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="oportunidades.php" class="notif-item">
                            <div class="notif-dot" style="background-color: transparent;"></div>
                            <div class="flex-grow-1">
                                <span class="category-badge bg-cand">Candidatura</span>
                                <div class="fw-semibold">Nova atualização na vaga "Base Academia SP".</div>
                                <small class="text-muted">Há 1 dia</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('markAllRead').addEventListener('click', function() {
            document.querySelectorAll('.notif-item.unread').forEach(item => {
                item.classList.remove('unread');
                item.querySelector('.notif-dot').style.backgroundColor = 'transparent';
            });
            this.innerHTML = '<i class="bi bi-check2-all me-2"></i> Tudo Lido!';
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-check2-all me-2"></i> Marcar todas como lidas';
            }, 3000);
        });
    </script>
</body>
</html>
