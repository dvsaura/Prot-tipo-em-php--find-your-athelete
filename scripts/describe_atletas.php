<?php
require __DIR__ . '/../config/conexao.php';
try {
    $stmt = $pdo->query('DESCRIBE atletas_perfil');
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo $c['Field'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
