<?php
require_once '../config/conexao.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? '';

function redirectToOpportunities($message = '') {
    $url = '../views/oportunidades.php';
    if ($message !== '') {
        $url .= '?msg=' . urlencode($message);
    }
    header('Location: ' . $url);
    exit();
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $requisitos = trim($_POST['requisitos'] ?? '');
    $idadeMin = intval($_POST['idade_min'] ?? 0);
    $idadeMax = intval($_POST['idade_max'] ?? 0);
    $pesoMin = floatval($_POST['peso_min'] ?? 0);
    $peDominante = trim($_POST['pe_dominante'] ?? 'ambos');
    $dataLimite = trim($_POST['data_limite'] ?? '');

    if ($titulo === '' || $categoria === '' || $dataLimite === '') {
        redirectToOpportunities('Preencha título, categoria e data limite para publicar.');
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO oportunidades (id_usuario_avaliador, titulo, categoria, requisitos, idade_min, idade_max, peso_min, pe_dominante_pref, data_limite) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $titulo, $categoria, $requisitos, $idadeMin > 0 ? $idadeMin : null, $idadeMax > 0 ? $idadeMax : null, $pesoMin > 0 ? $pesoMin : null, $peDominante, $dataLimite]);
        redirectToOpportunities('Oportunidade publicada com sucesso.');
    } catch (PDOException $e) {
        redirectToOpportunities('Erro ao publicar oportunidade: ' . $e->getMessage());
    }
}

if ($action === 'apply') {
    if (($_SESSION['user_tipo'] ?? 'atleta') !== 'atleta') {
        redirectToOpportunities('Apenas atletas podem se candidatar.');
    }

    $oppId = intval($_GET['id'] ?? 0);
    if ($oppId <= 0) {
        redirectToOpportunities('Vaga inválida.');
    }

    try {
        $stmtOpp = $pdo->prepare('SELECT id FROM oportunidades WHERE id = ?');
        $stmtOpp->execute([$oppId]);
        if (!$stmtOpp->fetch()) {
            redirectToOpportunities('Vaga não encontrada.');
        }

        $stmtCheck = $pdo->prepare('SELECT id FROM candidaturas WHERE id_oportunidade = ? AND id_usuario_atleta = ?');
        $stmtCheck->execute([$oppId, $userId]);
        if ($stmtCheck->fetch()) {
            redirectToOpportunities('Você já se candidatou a esta vaga.');
        }

        $stmtInsert = $pdo->prepare('INSERT INTO candidaturas (id_oportunidade, id_usuario_atleta, status) VALUES (?, ?, ?)');
        $stmtInsert->execute([$oppId, $userId, 'pendente']);
        redirectToOpportunities('Candidatura enviada com sucesso.');
    } catch (PDOException $e) {
        redirectToOpportunities('Erro ao enviar candidatura: ' . $e->getMessage());
    }
}

if ($action === 'manage') {
    $oppId = intval($_GET['id'] ?? 0);
    if ($oppId <= 0) {
        redirectToOpportunities('Vaga inválida.');
    }

    try {
        $stmtOwner = $pdo->prepare('SELECT id FROM oportunidades WHERE id = ? AND id_usuario_avaliador = ?');
        $stmtOwner->execute([$oppId, $userId]);
        if (!$stmtOwner->fetch()) {
            redirectToOpportunities('Você não pode visualizar candidaturas desta oportunidade.');
        }
    } catch (PDOException $e) {
        redirectToOpportunities('Erro ao validar acesso.');
    }

    header('Location: ../views/oportunidades.php?manage_id=' . $oppId);
    exit();
}

if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $oppId = intval($_POST['opportunity_id'] ?? 0);
    $candidatureId = intval($_POST['candidature_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'pendente');

    if ($oppId > 0 && $candidatureId > 0) {
        try {
            $stmtOwner = $pdo->prepare('SELECT id FROM oportunidades WHERE id = ? AND id_usuario_avaliador = ?');
            $stmtOwner->execute([$oppId, $userId]);
            if (!$stmtOwner->fetch()) {
                redirectToOpportunities('Você não pode alterar esta candidatura.');
            }

            $stmt = $pdo->prepare('UPDATE candidaturas SET status = ? WHERE id = ? AND id_oportunidade = ?');
            $stmt->execute([$status, $candidatureId, $oppId]);
            redirectToOpportunities('Status da candidatura atualizado.');
        } catch (PDOException $e) {
            redirectToOpportunities('Erro ao atualizar status.');
        }
    }
}

redirectToOpportunities();
