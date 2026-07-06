<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Login & Cadastro</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --fya-primary: #9ACD32; /* YellowGreen */
            --fya-dark-bg: #0B0B0C;
            --fya-dark-card: #252529;
            --fya-light-bg: #FFFFFF;
            --fya-light-card: #F5F5F5;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Inter', sans-serif;
            transition: background-color var(--transition-speed), color var(--transition-speed);
            overflow-x: hidden;
        }

        /* Split Canvas Layout */
        .auth-container {
            min-height: 100vh;
            display: flex;
        }

        .auth-image-section {
            flex: 1;
            background: url('https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
            position: relative;
            display: block;
        }

        .auth-image-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .auth-form-section {
            width: 450px;
            background-color: var(--fya-light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            transition: background-color var(--transition-speed);
        }

        [data-bs-theme="dark"] .auth-form-section {
            background-color: var(--fya-dark-bg);
        }

        /* Form Styling */
        .form-box {
            width: 100%;
            max-width: 350px;
        }

        .nav-pills .nav-link {
            color: #6c757d;
            font-weight: 600;
            transition: all var(--transition-speed);
        }

        .nav-pills .nav-link.active {
            background-color: var(--fya-primary) !important;
            color: #000 !important;
        }

        .btn-fya {
            background-color: var(--fya-primary);
            color: #000;
            font-weight: 600;
            border: none;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        }

        .btn-fya:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(154, 205, 50, 0.4);
            background-color: #8ab52b;
        }

        .form-control {
            border: 1px solid #E0E0E0;
            padding: 0.75rem 1rem;
        }

        [data-bs-theme="dark"] .form-control {
            background-color: var(--fya-dark-card);
            border-color: #3a3a40;
            color: white;
        }

        /* Theme Switcher */
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            cursor: pointer;
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            backdrop-filter: blur(5px);
            transition: background var(--transition-speed);
        }

        .theme-toggle:hover {
            background: rgba(255,255,255,0.4);
        }

        /* Responsividade */
        @media (max-width: 992px) {
            .auth-image-section { display: none; }
            .auth-form-section { width: 100%; }
        }
    </style>
</head>
<body>

    <button class="theme-toggle" id="themeToggle" title="Alternar Tema">
        <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
    </button>

    <div class="auth-container">
        <!-- Lado Esquerdo: Imagem de Impacto -->
        <section class="auth-image-section">
            <div class="auth-image-overlay">
                <div>
                    <h1 class="display-4 fw-bold">Find Your Athlete</h1>
                    <p class="lead">Find Your Athlete</p>
                    <span class="badge rounded-pill bg-success px-3 py-2">Democratizando o Talento</span>
                </div>
            </div>
        </section>

        <!-- Lado Direito: Formulários -->
        <main class="auth-form-section">
            <div class="form-box">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Bem-vindo!</h2>
                    <p class="text-muted">Acesse a maior vitrine de talentos do Brasil</p>
                </div>

                <!-- Abas de Alternância -->
                <ul class="nav nav-pills nav-fill mb-4" id="authTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab">Entrar</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab">Cadastrar</button>
                    </li>
                </ul>

                <div class="tab-content" id="authTabsContent">
                    <!-- Formulário de Login -->
                    <div class="tab-pane fade show active" id="pills-login" role="tabpanel">
                        <form action="../controllers/auth_controller.php?action=login" method="POST">
                            <div class="mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" name="senha" class="form-control" placeholder="********" required>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label" for="remember">Lembrar-me</label>
                                </div>
                                <a href="#" class="text-decoration-none small" data-bs-toggle="modal" data-bs-target="#modalRecuperarSenha" style="color: var(--fya-primary);">Esqueci minha senha</a>
                            </div>
                            <button type="submit" class="btn btn-fya w-100 py-2 mb-3">Acessar Conta</button>
                        </form>
                    </div>

                    <!-- Formulário de Cadastro -->
                    <div class="tab-pane fade" id="pills-register" role="tabpanel">
                        <form action="../controllers/auth_controller.php?action=register" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" name="nome" class="form-control" placeholder="Nome completo" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Conta</label>
                                <select name="tipo_conta" class="form-select">
                                    <option value="atleta">Atleta</option>
                                    <option value="avaliador">Avaliador</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto de Perfil (opcional)</label>
                                <input type="file" name="foto_perfil" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Senha</label>
                                <input type="password" name="senha" class="form-control" placeholder="Crie uma senha forte" required>
                            </div>
                            <button type="submit" class="btn btn-fya w-100 py-2 mb-3">Criar Conta Grátis</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="modalRecuperarSenha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Recuperar senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../controllers/auth_controller.php?action=forgot_password" method="POST">
                    <div class="modal-body">
                        <label class="form-label">E-mail cadastrado</label>
                        <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
                        <div class="small text-muted mt-2">Enviaremos instruções para redefinir sua senha.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-fya">Enviar link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript para Dual Theme -->
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        // Função para aplicar o tema
        function applyTheme(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('fya-theme', theme);
            
            if (theme === 'dark') {
                themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
            } else {
                themeIcon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
            }
        }

        // Carregar preferência salva
        const savedTheme = localStorage.getItem('fya-theme') || 'light';
        applyTheme(savedTheme);

        // Evento de clique para alternar
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(newTheme);
        });
    </script>
</body>
</html>
