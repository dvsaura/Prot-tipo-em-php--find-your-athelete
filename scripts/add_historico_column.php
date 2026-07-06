<?php
require __DIR__ . '/../config/conexao.php';
try {
    $pdo->exec("ALTER TABLE atletas_perfil ADD COLUMN IF NOT EXISTS historico_campeonatos TEXT NULL");
    echo "OK\n";
} catch (PDOException $e) {
    // MySQL older versions don't support IF NOT EXISTS in ALTER; try fallback
    try {
        $pdo->exec("ALTER TABLE atletas_perfil ADD COLUMN historico_campeonatos TEXT NULL");
        echo "OK\n";
    } catch (PDOException $e2) {
        echo 'ERROR: ' . $e2->getMessage() . PHP_EOL;
    }
}
