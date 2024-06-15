<?php
$host = 'localhost'; // o la IP del servidor de base de datos
$dbname = 'taller'; // nombre de tu base de datos
$username = 'root'; // nombre de usuario de la base de datos
$password = ''; // contraseña del usuario de la base de datos

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("No se pudo conectar a la base de datos $dbname :" . $e->getMessage());
}
?>
