<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/includes/header.php
 * Descrição: Barra superior com notificações, alternador de tema e perfil.
 */
require_once __DIR__ . '/../../config/conexao.php';

$userIdHeader = (int)($_SESSION['user_id'] ?? 0);
$notifCount = 0;
$recentNotifications = [];

if ($userIdHeader > 0) {
    $stmtNotif = $pdo->prepare("SELECT id, titulo, mensagem, categoria FROM notificacoes WHERE id_usuario = ? AND lida = 0 ORDER BY data_criacao DESC LIMIT 3");
    $stmtNotif->execute([$userIdHeader]);
    $recentNotifications = $stmtNotif->fetchAll();
    $notifCount = count($recentNotifications);
}
?>
<header class="navbar navbar-expand-lg sticky-top bg-body-tertiary border-bottom px-3 shadow-sm" style="height:70px; z-index: 1040; position: sticky; top: 0;">
    <div class="container-fluid">
        <!-- Espaçador para alinhar com o sidebar retrátil -->
        <div class="ms-auto d-flex align-items-center gap-3">
            
            <a href="buscar_atletas.php" class="btn btn-sm btn-outline-secondary d-none d-lg-inline-flex align-items-center">
                <i class="bi bi-stars me-1"></i> Explorar
            </a>

            <!-- Alternador de Tema (Sol/Lua) -->
            <button class="btn btn-link nav-link p-0 text-body" id="themeToggle" title="Alternar Tema">
                <i class="bi bi-moon-stars-fill" id="themeIcon" style="font-size: 1.2rem;"></i>
            </button>

            <!-- Notificações -->
            <div class="dropdown">
                <a href="notificacoes.php" class="nav-link position-relative" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            <?php echo $notifCount; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="notifDropdown" style="width: 320px;">
                    <li class="dropdown-header fw-bold">Notificações</li>
                    <?php if (!empty($recentNotifications)): ?>
                        <?php foreach ($recentNotifications as $notification): ?>
                            <li><a class="dropdown-item py-2" href="notificacoes.php"><i class="bi bi-bell-fill me-2 text-primary"></i> <?php echo htmlspecialchars(mb_strimwidth($notification['titulo'] ?: $notification['mensagem'] ?: 'Nova atividade', 0, 60, '...')); ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><span class="dropdown-item py-2 text-muted">Nenhuma notificação nova no momento.</span></li>
                    <?php endif; ?>
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
