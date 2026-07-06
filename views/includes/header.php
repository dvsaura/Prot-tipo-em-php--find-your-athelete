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
            <img src="https://cdn-icons-png.flaticon.com/512/857/857451.png" alt="Logo" style="width: 34px; height: 34px; object-fit: cover;" class="me-2">
            <div class="d-none d-md-block">
                <div>FIND YOUR ATHLETE</div>
                <small class="text-muted">Find Your Athlete</small>
            </div>
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
            applyTheme(currentTheme === 'light' ? 'dark' : 'light');
        });
    })();
</script>
