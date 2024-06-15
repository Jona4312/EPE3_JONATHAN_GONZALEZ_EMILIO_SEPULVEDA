<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Taller Mecánico</title>
    <link rel="stylesheet" href="css\style1.css">
</head>
<body>
    <form action="login.php" method="post">
        <h2>Iniciar Sesión</h2>
        <input type="email" name="correo" placeholder="Correo" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>

<?php
include 'conexion.php'; // Incluye la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ? AND contraseña = ?");
    $stmt->execute([$correo, $contrasena]);
    $user = $stmt->fetch();

    if ($user) {
        session_start();
        $_SESSION['rut'] = $user['Rut'];
        $_SESSION['Tipo'] = $user['Tipo'];

        if ($user['Tipo'] == 'Administrador') {
            header("Location: ADMINISTRADOR\panel.php");
        } else if ($user['Tipo'] == 'Vendedor') {
            header("Location: vendedor/control.php");
        }
    } else {
        echo "<p>Credenciales incorrectas.</p>";
    }
}
?>

