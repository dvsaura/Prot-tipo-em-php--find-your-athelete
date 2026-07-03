<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/includes/header.php
 * Descrição: Barra superior com notificações, alternador de tema e perfil.
 */
?>
<header class="navbar navbar-expand-lg sticky-top bg-body-tertiary border-bottom px-3 shadow-sm" style="height:70px;">
    <div class="container-fluid">
        <!-- Espaçador para alinhar com o sidebar retrátil -->
        <div class="ms-auto d-flex align-items-center gap-3">
            
            <!-- Alternador de Tema (Sol/Lua) -->
            <button class="btn btn-link nav-link p-0 text-body" id="themeToggle" title="Alternar Tema">
                <i class="bi bi-moon-stars-fill" id="themeIcon" style="font-size: 1.2rem;"></i>
            </button>

            <!-- Notificações -->
            <div class="dropdown">
                <a href="notificacoes.php" class="nav-link position-relative" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        3
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="notifDropdown" style="width: 300px;">
                    <li class="dropdown-header fw-bold">Notificações</li>
                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-envelope me-2 text-primary"></i> Nova mensagem de Clube X</a></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-eye me-2 text-success"></i> Seu perfil foi visto por um olheiro</a></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-check-circle me-2 text-warning"></i> Status de candidatura atualizado</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center small text-muted" href="notificacoes.php">Ver todas as notificações</a></li>
                </ul>
            </div>

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
                    <span class="ms-2 d-none d-md-inline text-body fw-semibold small">Minha Conta</span>
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
            applyTheme(currentTheme === 'light' ? 'dark' : 'light');
        });
    })();
</script>
