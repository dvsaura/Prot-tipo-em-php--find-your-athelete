<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: views/mensagens.php
 * Descrição: Caixa de entrada e chat assíncrono entre atletas e avaliadores.
 */
session_start();
require_once '../config/conexao.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$selectedContactId = intval($_GET['contact'] ?? 0);

try {
    $stmtContacts = $pdo->prepare(
        "SELECT u.id, u.nome, a.foto_perfil, " .
        "(SELECT MAX(m.data_envio) FROM mensagens m WHERE (m.id_remetente = u.id AND m.id_destinatario = ?) OR (m.id_remetente = ? AND m.id_destinatario = u.id)) AS ultima_data, " .
        "(SELECT m2.mensagem FROM mensagens m2 WHERE (m2.id_remetente = u.id AND m2.id_destinatario = ?) OR (m2.id_remetente = ? AND m2.id_destinatario = u.id) ORDER BY m2.data_envio DESC LIMIT 1) AS ultima_msg, " .
        "(SELECT COUNT(*) FROM mensagens m3 WHERE m3.id_remetente = u.id AND m3.id_destinatario = ? AND m3.lida = 0) AS unread_count " .
        "FROM usuarios u " .
        "LEFT JOIN atletas_perfil a ON a.id_usuario = u.id " .
        "WHERE u.id != ? " .
        "ORDER BY IFNULL(ultima_data, '1970-01-01') DESC, u.nome ASC " .
        "LIMIT 30"
    );
    $stmtContacts->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
    $contacts = $stmtContacts->fetchAll();
} catch (PDOException $e) {
    $contacts = [];
}

if ($selectedContactId <= 0 && !empty($contacts)) {
    $selectedContactId = $contacts[0]['id'];
}

$selectedContact = null;
$conversation = [];

if ($selectedContactId > 0) {
    try {
        $stmtContact = $pdo->prepare("SELECT u.id, u.nome, a.foto_perfil FROM usuarios u LEFT JOIN atletas_perfil a ON a.id_usuario = u.id WHERE u.id = ?");
        $stmtContact->execute([$selectedContactId]);
        $selectedContact = $stmtContact->fetch();

        if ($selectedContact) {
            $stmtMarkRead = $pdo->prepare(
                "UPDATE mensagens SET lida = 1 WHERE id_destinatario = ? AND id_remetente = ?"
            );
            $stmtMarkRead->execute([$userId, $selectedContactId]);

            $stmtConversation = $pdo->prepare(
                "SELECT m.*, u.nome AS remetente_nome FROM mensagens m " .
                "JOIN usuarios u ON u.id = m.id_remetente " .
                "WHERE (m.id_remetente = ? AND m.id_destinatario = ?) OR (m.id_remetente = ? AND m.id_destinatario = ?) " .
                "ORDER BY m.data_envio ASC"
            );
            $stmtConversation->execute([$userId, $selectedContactId, $selectedContactId, $userId]);
            $conversation = $stmtConversation->fetchAll();
        }
    } catch (PDOException $e) {
        $selectedContact = null;
        $conversation = [];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYA - Mensagens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --fya-primary: #9ACD32;
            --transition-speed: 0.3s;
        }

        html, body { min-height: 100%; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bs-body-bg);
            min-height: 100vh;
        }

        #main-content {
            padding-top: 70px;
            min-height: calc(100vh - 70px);
        }   

        /* Layout de Duas Colunas */
        .chat-container {
            height: calc(100vh - 140px);
            background-color: var(--bs-body-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        /* Coluna da Esquerda: Lista de Conversas */
        .chat-list {
            width: 350px;
            border-right: 1px solid var(--bs-border-color);
            background-color: var(--bs-tertiary-bg);
            display: flex;
            flex-direction: column;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            cursor: pointer;
            transition: background var(--transition-speed);
            border-bottom: 1px solid var(--bs-border-color);
            text-decoration: none;
            color: inherit;
        }

        .conversation-item:hover {
            background-color: rgba(154, 205, 50, 0.1);
        }

        .conversation-item.active {
            background-color: rgba(154, 205, 50, 0.2);
            border-left: 4px solid var(--fya-primary);
        }

        .avatar-container {
            position: relative;
            margin-right: 12px;
        }

        .avatar-container img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background-color: #28a745;
            border: 2px solid var(--bs-body-bg);
            border-radius: 50%;
        }

        /* Coluna da Direita: Janela de Chat */
        .chat-window {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--bs-body-bg);
        }

        .chat-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--bs-border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* Balões de Mensagens */
        .message {
            max-width: 70%;
            padding: 0.8rem 1rem;
            border-radius: 15px;
            font-size: 0.9rem;
            position: relative;
            line-height: 1.4;
        }

        .message.received {
            align-self: flex-start;
            background-color: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            border-bottom-left-radius: 2px;
        }

        .message.sent {
            align-self: flex-end;
            background-color: var(--fya-primary);
            color: #000;
            border-bottom-right-radius: 2px;
            font-weight: 500;
        }

        .message-time {
            display: block;
            font-size: 0.7rem;
            text-align: right;
            margin-top: 4px;
            opacity: 0.7;
        }

        /* Input de Mensagem */
        .chat-input-area {
            padding: 1.5rem;
            border-top: 1px solid var(--bs-border-color);
            display: flex;
            gap: 10px;
        }

        .chat-input-area input {
            border-radius: 50px;
            padding-left: 1.2rem;
        }

        .btn-send {
            background-color: var(--fya-primary);
            color: #000;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: transform var(--transition-speed);
        }

        .btn-send:hover {
            transform: scale(1.1);
            background-color: #8ab52b;
        }

        @media (max-width: 768px) {
            .chat-list { width: 100px; }
            .sidebar-text, .conversation-info { display: none; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main class="container-fluid p-4">
            <h3 class="fw-bold mb-4">Mensagens <span class="text-muted fs-6 fw-normal">/ Conversas</span></h3>

                    <div class="chat-container d-flex">
                        <!-- Coluna Esquerda: Lista de Conversas -->
                        <aside class="chat-list">
                            <div class="p-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-body border-end-0"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control border-start-0" placeholder="Buscar contato..." disabled>
                                </div>
                            </div>
                            
                            <div class="overflow-auto">
                                <?php if (!empty($contacts)): ?>
                                    <?php foreach ($contacts as $contact): ?>
                                        <a href="mensagens.php?contact=<?php echo intval($contact['id']); ?>" class="conversation-item <?php echo $contact['id'] === $selectedContactId ? 'active' : ''; ?>">
                                            <div class="avatar-container">
                                                <img src="<?php echo !empty($contact['foto_perfil']) ? '../uploads/'.htmlspecialchars($contact['foto_perfil']) : 'https://ui-avatars.com/api/?name='.urlencode($contact['nome']).'&background=9ACD32&color=fff'; ?>" alt="Avatar">
                                                <span class="online-indicator"></span>
                                            </div>
                                            <div class="conversation-info overflow-hidden">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold small"><?php echo htmlspecialchars($contact['nome']); ?></span>
                                                    <span class="text-muted" style="font-size: 0.7rem;"><?php echo !empty($contact['ultima_data']) ? date('H:i', strtotime($contact['ultima_data'])) : '---'; ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($contact['ultima_msg'] ?: 'Sem mensagens ainda'); ?></span>
                                                    <?php if (!empty($contact['unread_count'])): ?>
                                                        <span class="badge bg-danger rounded-pill" style="font-size: 0.6rem;"><?php echo intval($contact['unread_count']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted small">
                                        Nenhum contato encontrado. Convide outros atletas ou avaliadores para começar a conversar.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </aside>

                        <!-- Coluna Direita: Janela de Chat -->
                        <section class="chat-window">
                            <?php if ($selectedContact): ?>
                                <div class="chat-header">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo !empty($selectedContact['foto_perfil']) ? '../uploads/'.htmlspecialchars($selectedContact['foto_perfil']) : 'https://ui-avatars.com/api/?name='.urlencode($selectedContact['nome']).'&background=9ACD32&color=fff'; ?>" class="rounded-circle me-3" style="width: 40px; height: 40px;" alt="Avatar">
                                        <div>
                                            <h6 class="fw-bold m-0"><?php echo htmlspecialchars($selectedContact['nome']); ?></h6>
                                            <small class="text-success">Online agora</small>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Ver Perfil</a></li>
                                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-block me-2"></i> Bloquear</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="chat-messages" id="chatMessages">
                                    <?php if (!empty($conversation)): ?>
                                        <?php foreach ($conversation as $message): ?>
                                            <div class="message <?php echo $message['id_remetente'] === $userId ? 'sent' : 'received'; ?>">
                                                <?php echo nl2br(htmlspecialchars($message['mensagem'])); ?>
                                                <span class="message-time"><?php echo date('H:i', strtotime($message['data_envio'])); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted small mt-5">Nenhuma mensagem nesta conversa ainda. Envie a primeira mensagem agora.</div>
                                    <?php endif; ?>
                                </div>

                                <form action="../controllers/messages_controller.php?action=send" method="POST" class="chat-input-area">
                                    <input type="hidden" name="contact_id" value="<?php echo intval($selectedContact['id']); ?>">
                                    <input type="text" id="messageInput" name="mensagem" class="form-control" placeholder="Digite sua mensagem..." autocomplete="off">
                                    <button class="btn-send" type="submit"><i class="bi bi-send-fill"></i></button>
                                </form>
                            <?php else: ?>
                                <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1 text-center p-4">
                                    <i class="bi bi-chat-dots fs-1 mb-3 text-muted"></i>
                                    <h5 class="fw-bold">Selecione um contato</h5>
                                    <p class="text-muted">Escolha um atleta ou avaliador à esquerda para iniciar a conversa.</p>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        async function atualizarDados() {

        try{
            const resposta = await fetch('../controllers/messages_controller.php?action=fetch&contact_id=<?php echo $selectedContactId; ?>');
            const dados = await resposta.json();

            document.getElementById('Mensagens').innerHTML = dados.conversationHTML;
            document.getElementById('chatMessages').scrollLeft = document.getElementById('chatMessages').scrollWidth;
        }catch(error){
            console.error('Erro ao atualizar dados: ',error);
        }
        }
    </script>
</body>
</html>
