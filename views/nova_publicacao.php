<?php
session_start();
require_once '../config/conexao.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Simples formulário de criação que envia para post_controller.php?action=create
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Publicação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        #main-content { padding-top: 70px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div id="main-content">
        <?php include 'includes/header.php'; ?>
        <main class="container p-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="fw-bold mb-3">Adicionar Nova Publicação</h4>
                            <form action="../controllers/post_controller.php?action=create" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Título</label>
                                    <input type="text" name="titulo_publicacao" class="form-control" placeholder="Título (opcional)">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Descrição</label>
                                    <textarea name="descricao_publicacao" class="form-control" rows="5" placeholder="Escreva o conteúdo da publicação"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Imagem (opcional)</label>
                                    <input type="file" name="imagem_publicacao" accept="image/*" class="form-control">
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="feed.php" class="btn btn-outline-secondary">Voltar ao Feed</a>
                                    <button type="submit" class="btn btn-fya">Publicar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
