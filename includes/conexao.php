<?php
// Arquivo: C:\xampp\htdocs\juridico\includes\conexao.php

$host = 'localhost';
$db   = 'portal_juridico'; // O nome do seu novo banco de dados
$user = 'root'; // Usuário padrão do XAMPP
$pass = '';     // Senha padrão do XAMPP é vazia
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>