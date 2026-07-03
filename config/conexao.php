<?php
/**
 * FYA - Find Your Athlete
 * Arquivo: config/conexao.php
 * Descrição: Gerencia a conexão com o banco de dados MySQL utilizando PDO.
 */



// Configurações do Banco de Dados
$host = 'tini.click';
$db   = 'findmyathlete_db';
$user = 'findmyathlete_db'; // Ajustar conforme o ambiente do usuário
$pass = '54c98b46e8178f4475de2c4334a72b5!';     // Ajustar conforme o ambiente do usuário
$charset = 'utf8mb4';

// DSN (Data Source Name) para conexão PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opções do PDO para maior segurança e facilidade de debug
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna arrays associativos por padrão
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desativa emulação para evitar SQL Injection
];

try {
    // Cria a instância de conexão
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Em caso de erro, interrompe a execução e exibe a mensagem
    die("Erro crítico na conexão com o banco de dados: " . $e->getMessage());
}
?> 