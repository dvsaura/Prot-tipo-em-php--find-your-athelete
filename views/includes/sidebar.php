<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/includes/sidebar.php
 * Descrição: Menu lateral esquerdo retrátil com navegação principal.
 */
?>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<style>
    :root {
        --fya-primary: #9ACD32;
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 64px;
        --transition-speed: 0.3s;
    }

    /* Reset global para evitar espaços em branco inesperados no topo */
    html, body { margin: 0; padding: 0; }

    #sidebar {
        width: var(--sidebar-width);
        min-height: 100vh;
        transition: width var(--transition-speed) ease;
        background-color: var(--bs-tertiary-bg);
        border-right: 1px solid var(--bs-border-color);
        z-index: 1000;
        position: fixed;
        left: 0;
        top: 0;
        padding-top: 0.75rem;
    }

    #sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .sidebar-brand {
        padding: 1rem;
        display: flex;
        align-items: center;
        overflow: hidden;
        white-space: nowrap;
        text-decoration: none;
        color: inherit;
        margin-bottom: 2rem;
    }

    .sidebar-brand img {
        width: 32px;
        height: 32px;
        min-width: 32px;
        margin-right: 12px;
    }

    .nav-link-fya {
        display: flex;
        align-items: center;
        padding: 0.8rem 1rem;
        color: var(--bs-body-color);
        text-decoration: none;
        transition: all var(--transition-speed);
        border-left: 3px solid transparent;
        white-space: nowrap;
        font-weight: 500;
        border-radius: 12px;
        margin: 0.2rem 0.5rem;
    }

    .nav-link-fya:hover {
        background-color: rgba(154, 205, 50, 0.1);
        color: var(--fya-primary);
    }

    .nav-link-fya.active {
        background-color: rgba(154, 205, 50, 0.15);
        color: var(--fya-primary);
        border-left: 3px solid var(--fya-primary);
    }

    .nav-link-fya i {
        font-size: 1.2rem;
        min-width: 32px;
    }

    .sidebar-text {
        transition: opacity var(--transition-speed);
        opacity: 1;
    }

    #sidebar.collapsed .sidebar-text, 
    #sidebar.collapsed .brand-text {
        opacity: 0;
        pointer-events: none;
        display: none;
    }

    .sidebar-footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        padding: 1rem;
        border-top: 1px solid var(--bs-border-color);
    }

    #main-content {
        margin-left: var(--sidebar-width);
        transition: margin-left var(--transition-speed) ease;
    }

    #main-content.expanded {
        margin-left: var(--sidebar-collapsed-width);
    }

    @media (max-width: 992px) {
        #sidebar {
            position: static;
            width: 100%;
            min-height: auto;
            padding-top: 0;
            border-right: none;
            border-bottom: 1px solid var(--bs-border-color);
        }

        #sidebar.collapsed {
            width: 100%;
        }

        .sidebar-brand {
            justify-content: center;
            padding: 0.75rem 1rem;
        }

        .sidebar-footer {
            position: static;
            border-top: none;
            padding: 1rem;
        }

        #main-content,
        #main-content.expanded {
            margin-left: 0;
        }
    }
</style>

<nav id="sidebar">
    <!-- Logo -->
    <a href="feed.php" class="sidebar-brand">
        <img src="https://cdn-icons-png.flaticon.com/512/857/857451.png" alt="Logo">
        <span class="brand-text fw-bold fs-5 text-uppercase">Find Your Athlete <small class="d-block fs-6 fw-normal text-muted">Find Your Athlete</small></span>
    </a>

    <!-- Links de Navegação -->
    <div class="nav flex-column">
        <a href="feed.php" class="nav-link-fya <?php echo $currentPage === 'feed.php' ? 'active' : ''; ?>">
            <i class="bi bi-house-door"></i>
            <span class="sidebar-text">Início (Feed)</span>
        </a>
        <a href="buscar_atletas.php" class="nav-link-fya <?php echo $currentPage === 'buscar_atletas.php' ? 'active' : ''; ?>">
            <i class="bi bi-search"></i>
            <span class="sidebar-text">Buscar Atletas</span>
        </a>
        <a href="oportunidades.php" class="nav-link-fya <?php echo $currentPage === 'oportunidades.php' ? 'active' : ''; ?>">
            <i class="bi bi-trophy"></i>
            <span class="sidebar-text">Oportunidades</span>
        </a>
        <a href="mensagens.php" class="nav-link-fya position-relative <?php echo $currentPage === 'mensagens.php' ? 'active' : ''; ?>">
            <i class="bi bi-chat-dots"></i>
            <span class="sidebar-text">Mensagens</span>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger sidebar-text" style="font-size: 0.6rem;">5</span>
        </a>
        <a href="notificacoes.php" class="nav-link-fya position-relative <?php echo $currentPage === 'notificacoes.php' ? 'active' : ''; ?>">
            <i class="bi bi-bell"></i>
            <span class="sidebar-text">Notificações</span>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger sidebar-text" style="font-size: 0.6rem;">3</span>
        </a>
        <a href="perfil_atleta.php" class="nav-link-fya <?php echo $currentPage === 'perfil_atleta.php' ? 'active' : ''; ?>">
            <i class="bi bi-person-badge"></i>
            <span class="sidebar-text">Meu Perfil</span>
        </a>
        <a href="configuracoes.php" class="nav-link-fya <?php echo $currentPage === 'configuracoes.php' ? 'active' : ''; ?>">
            <i class="bi bi-gear"></i>
            <span class="sidebar-text">Configurações</span>
        </a>
    </div>

    <!-- Footer do Menu -->
    <div class="sidebar-footer">
        <button id="toggleSidebar" class="btn btn-sm btn-outline-secondary w-100 mb-2">
            <i class="bi bi-list"></i> <span class="sidebar-text ms-1">Recolher</span>
        </button>
        <a href="../controllers/auth_controller.php?action=logout" class="nav-link-fya text-danger p-0">
            <i class="bi bi-box-arrow-right"></i>
            <span class="sidebar-text">Sair</span>
        </a>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            const isCollapsed = sidebar.classList.contains('collapsed');
            toggleBtn.innerHTML = isCollapsed 
                ? '<i class="bi bi-list"></i> <span class="sidebar-text ms-1">Expandir</span>' 
                : '<i class="bi bi-list"></i> <span class="sidebar-text ms-1">Recolher</span>';
        });
    });
</script>
