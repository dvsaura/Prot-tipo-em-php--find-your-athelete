<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/includes/header.php
 * Descrição: Barra superior com alternador de tema e perfil.
 */
?>
<header class="navbar navbar-expand-lg bg-body-tertiary border-bottom px-3 shadow-sm" style="height:70px; z-index: 1040; position: fixed; top: 0; left: 0; width: 100vw;">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3">
        <a href="feed.php" class="d-flex align-items-center text-decoration-none text-body fw-semibold">
            <?php
                // Prefer uploaded app logo if present, otherwise fallback to CDN icon
                $appLogoPath = __DIR__ . '/../../uploads/foto.png';
                $appLogoWeb = file_exists($appLogoPath) ? '../uploads/foto.png' : 'https://cdn-icons-png.flaticon.com/512/857/857451.png';
            ?>
            <img src="<?php echo htmlspecialchars($appLogoWeb); ?>" alt="Find Your Athlete" style="height: 60px; width: auto; object-fit: contain;" class="me-2">
            <span class="visually-hidden">Find Your Athlete</span>
        </a>

        <div class="d-flex align-items-center gap-3 ms-auto">
            
            <a href="buscar_atletas.php" class="btn btn-sm btn-outline-secondary d-none d-lg-inline-flex align-items-center">
                <i class="bi bi-stars me-1"></i> Explorar
            </a>

            <!-- Alternador de Tema (Sol/Lua) -->
            <button class="btn btn-link nav-link p-0 text-body" id="themeToggle" title="Alternar Tema">
                <i class="bi bi-moon-stars-fill" id="themeIcon" style="font-size: 1.2rem;"></i>
            </button>

            <!-- Avatar do Usuário -->
            <div class="dropdown">
                <a href="perfil_atleta.php" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php
                        $userAvatar = $_SESSION['user_avatar'] ?? '';
                        $userName = $_SESSION['user_nome'] ?? 'Usuario FYA';
                        $avatarSrc = '';
                        if (!empty($userAvatar) && file_exists(__DIR__ . '/../../uploads/' . $userAvatar)) {
                            $avatarSrc = '../uploads/' . $userAvatar;
                        } else {
                            $avatarSrc = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=9ACD32&color=fff';
                        }
                    ?>
                    <img src="<?php echo htmlspecialchars($avatarSrc); ?>" 
                         alt="Avatar" class="rounded-circle border" style="width: 35px; height: 35px; object-fit: cover;">
                    <span class="ms-2 d-none d-md-inline text-body fw-semibold small"><?php echo htmlspecialchars($_SESSION['user_nome'] ?? 'Minha Conta'); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item" href="perfil_atleta.php"><i class="bi bi-person me-2"></i> Meu Perfil</a></li>
                    <li><a class="dropdown-item" href="configuracoes.php"><i class="bi bi-gear me-2"></i> Configurações</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../controllers/auth_controller.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                </ul>
            </div>

        </div>
    </div>
</header>

<!-- JavaScript do Theme Toggle integrado ao Header para modularidade -->
<script>
    (function() {
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        function applyTheme(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('fya-theme', theme);
            if (theme === 'dark') {
                themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
            } else {
                themeIcon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
            }
        }

        const savedTheme = localStorage.getItem('fya-theme') || 'light';
        applyTheme(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const next = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(next);
        });
    })();
</script>

<!-- (Favicon handling removed per revert request) -->

<!-- Dark theme overrides (unifica cores do perfil para todo o app) -->
<style>
    [data-bs-theme="dark"] body { background-color: #071018; color: #e2e8f0; }
    [data-bs-theme="dark"] .profile-cover { background: linear-gradient(135deg, #0f172a, #071014); }
    [data-bs-theme="dark"] .section-card,
    [data-bs-theme="dark"] .card,
    [data-bs-theme="dark"] .card-body,
    [data-bs-theme="dark"] .card-footer,
    [data-bs-theme="dark"] .dropdown-menu,
    [data-bs-theme="dark"] .modal-content { background-color: #0f172a; color: #e2e8f0; }

    [data-bs-theme="dark"] .config-card { background-color: #0f172a; }
    [data-bs-theme="dark"] .stat-pill { background: #1f2937; color: #e2e8f0; }
    [data-bs-theme="dark"] .stat-pill strong { color: #9ACD32; }

    [data-bs-theme="dark"] .btn-fya { background-color: #9ACD32; color: #000; }
    [data-bs-theme="dark"] .btn-fya:hover { background-color: #8fb52a; }

    [data-bs-theme="dark"] .btn-outline-secondary,
    [data-bs-theme="dark"] .btn-outline-light,
    [data-bs-theme="dark"] .btn-outline-dark { color: #d1d5db; border-color: rgba(209,213,219,0.07); }

    [data-bs-theme="dark"] .navbar,
    [data-bs-theme="dark"] .bg-body-tertiary { background-color: rgba(15,17,26,0.65) !important; backdrop-filter: blur(6px); }

    [data-bs-theme="dark"] .sidebar { background-color: #071018; }
    [data-bs-theme="dark"] .search-container { background-color: #071018; }

    /* ensure modals, dropdowns, menus are readable */
    [data-bs-theme="dark"] .dropdown-menu, [data-bs-theme="dark"] .modal-content { box-shadow: 0 8px 24px rgba(0,0,0,0.6); }
</style>

<!-- Override for elements using inline linear-gradients or light backgrounds -->
<style>
    /* Target elements that contain inline linear-gradient styles and force a dark-friendly gradient */
    [data-bs-theme="dark"] [style*="linear-gradient"] {
        background: linear-gradient(135deg, rgba(15,17,26,0.72), rgba(7,16,24,0.5)) !important;
        color: #e2e8f0 !important;
    }

    /* Specific overrides for cards/alerts with inline backgrounds */
    [data-bs-theme="dark"] .card[style*="background"] ,
    [data-bs-theme="dark"] .alert[style*="background"] ,
    [data-bs-theme="dark"] .card[style*="background:"] {
        background-color: #0f172a !important;
        background-image: none !important;
        color: #e2e8f0 !important;
    }

    /* Inputs / form controls in dark mode */
    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select { background-color: #071018; color: #e2e8f0; border-color: rgba(209,213,219,0.06); }

    /* Ensure small components like search group blend in */
    [data-bs-theme="dark"] .search-container,
    [data-bs-theme="dark"] .edit-card,
    [data-bs-theme="dark"] .athlete-card { background-color: #071018 !important; border-color: rgba(255,255,255,0.04) !important; }
</style>

<!-- Sidebar & Messages specific dark overrides -->
<style>
    [data-bs-theme="dark"] #sidebar {
        background-color: #071018 !important;
        border-right: 1px solid rgba(255,255,255,0.04) !important;
    }

    [data-bs-theme="dark"] .nav-link-fya {
        color: #e2e8f0 !important;
        background: transparent !important;
    }

    [data-bs-theme="dark"] .nav-link-fya:hover {
        background-color: rgba(154,205,50,0.06) !important;
        color: #9ACD32 !important;
    }

    [data-bs-theme="dark"] .nav-link-fya.active {
        background-color: rgba(154,205,50,0.12) !important;
        color: #9ACD32 !important;
        border-left: 3px solid #9ACD32 !important;
    }

    [data-bs-theme="dark"] .sidebar-footer { border-top-color: rgba(255,255,255,0.04); }

    /* Mensagens */
    [data-bs-theme="dark"] .chat-container { background-color: #071018 !important; }
    [data-bs-theme="dark"] .chat-list { background-color: #071018 !important; border-right: 1px solid rgba(255,255,255,0.04) !important; }
    [data-bs-theme="dark"] .conversation-item { color: #e2e8f0 !important; border-bottom: 1px solid rgba(255,255,255,0.04) !important; }
    [data-bs-theme="dark"] .conversation-item:hover { background-color: rgba(154,205,50,0.06) !important; }
    [data-bs-theme="dark"] .conversation-item.active { background-color: rgba(154,205,50,0.12) !important; border-left: 4px solid #9ACD32 !important; }
    [data-bs-theme="dark"] .chat-window { background-color: #071018 !important; }
    [data-bs-theme="dark"] .chat-header, [data-bs-theme="dark"] .chat-input-area { background-color: #0f172a !important; border-color: rgba(255,255,255,0.05) !important; }
    [data-bs-theme="dark"] .message.received { background-color: #0f172a !important; color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .message.sent { background-color: #9ACD32 !important; color: #000 !important; }
    [data-bs-theme="dark"] .online-indicator { border: 2px solid #071018 !important; }
    [data-bs-theme="dark"] .badge.bg-danger { background-color: #b91c1c !important; }
</style>
