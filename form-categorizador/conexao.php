<?php
$host = 'localhost';
$db = 'form_categorizador';
$user = 'root';
$pass = '';  // string vazia em vez de null

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro: Não foi possível conectar. " . $e->getMessage());
}
?>
