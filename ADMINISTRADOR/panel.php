<?php
session_start();
if (!isset($_SESSION['Tipo']) || $_SESSION['Tipo'] != 'Administrador') {
    header('Location: /login.php');
    exit();
}

include '../conexion.php';

// Función para obtener usuarios filtrados
function getUsuariosFiltrados($pdo, $filtro) {
    $sql = "SELECT * FROM usuarios WHERE 
            Rut LIKE :filtro OR 
            Correo LIKE :filtro";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':filtro', '%' . $filtro . '%', PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener todos los usuarios
function getUsuarios($pdo) {
    $stmt = $pdo->query("SELECT * FROM usuarios");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Insertar un nuevo usuario
if (isset($_POST['add'])) {
    $Rut = preg_replace('/[^0-9]/', '', $_POST['Rut']); // Eliminar caracteres no numéricos
    $Correo = $_POST['Correo'];
    $Password = password_hash($_POST['Password'], PASSWORD_DEFAULT); // Hashear la contraseña

    // Verificar si el Rut ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE Rut = ?");
    $stmt->execute([$Rut]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (Rut, Correo, Contraseña) VALUES (?, ?, ?)");
        $stmt->execute([$Rut, $Correo, $Password]);

        $_SESSION['message'] = "Usuario agregado con éxito.";
        header("Location: panel.php");
        exit();
    } else {
        $error = "El Rut ya existe.";
    }
}

// Eliminar un usuario
if (isset($_GET['delete'])) {
    $Rut = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE Rut = ?");
    $stmt->execute([$Rut]);

    $_SESSION['message'] = "Usuario eliminado con éxito.";
    header("Location: panel.php");
    exit();
}

// Actualizar un usuario
if (isset($_POST['update'])) {
    $Rut = $_POST['Rut'];
    $Correo = $_POST['Correo_' . $Rut];

    $stmt = $pdo->prepare("UPDATE usuarios SET Correo = ? WHERE Rut = ?");
    $stmt->execute([$Correo, $Rut]);

    $_SESSION['message'] = "Usuario actualizado con éxito.";
    header("Location: panel.php");
    exit();
}

// Determinar si hay un filtro aplicado
$filtro = '';
if (isset($_POST['buscar'])) {
    $filtro = $_POST['filtro'];
    $usuarios = getUsuariosFiltrados($pdo, $filtro);
} else {
    $usuarios = getUsuarios($pdo);
}

// Función para formatear RUT
function formatearRut($rut) {
    $rut = preg_replace('/[^0-9kK]/', '', $rut);
    if (strlen($rut) < 8) {
        return $rut;
    }
    $numero = substr($rut, 0, strlen($rut) - 1);
    $dv = strtoupper(substr($rut, -1));
    return number_format($numero, 0, '', '.') . '-' . $dv;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRUD de Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
            position: relative; /* Añadido para contener el top-right-corner */
        }

        h1, h2 {
            color: #444;
        }

        form {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form input[type="text"], form input[type="email"], form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        form button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #5C6BC0;
            color: #fff;
            cursor: pointer;
            margin-right: 10px;
        }

        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        table th {
            background-color: #5C6BC0;
            color: #fff;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table a {
            color: #f00;
            text-decoration: none;
            margin-left: 10px;
        }

        .login-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #5C6BC0;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .top-right-corner {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .error, .message {
            margin: 20px auto;
            text-align: center;
        }

        .error {
            color: red;
        }

        .message {
            color: green;
        }
    </style>
</head>
<body>
    <div class="top-right-corner">
        <a class="login-button" href="../login.php">Volver al Login</a>
    </div>

    <h1>Administracion de Usuarios</h1>

    <?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
    <div class="message"><?= htmlspecialchars($_SESSION['message']); ?></div>
    <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- Formulario para agregar usuarios -->
    <form action="panel.php" method="post">
        <input type="text" name="Rut" placeholder="Rut" required>
        <input type="email" name="Correo" placeholder="Correo electrónico" required>
        <input type="password" name="Password" placeholder="Contraseña" required>
        <button type="submit" name="add">Agregar Usuario</button>
    </form>

    <hr>

    <!-- Formulario para buscar usuarios -->
    <form action="panel.php" method="post">
        <input type="text" name="filtro" placeholder="Buscar por RUT o correo">
        <button type="submit" name="buscar">Buscar</button>
    </form>

    <hr>

    <!-- Tabla para mostrar usuarios -->
    <table>
        <thead>
            <tr>
                <th>Rut</th>
                <th>Correo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars(formatearRut($usuario['Rut'])); ?></td>
                <td><?= htmlspecialchars($usuario['Correo']); ?></td>
                <td>
                    <form action="panel.php" method="post" style="display: inline;">
                        <input type="hidden" name="Rut" value="<?= htmlspecialchars($usuario['Rut']); ?>">
                        <input type="email" name="Correo_<?= htmlspecialchars($usuario['Rut']); ?>" value="<?= htmlspecialchars($usuario['Correo']); ?>">
                        <button type="submit" name="update">Actualizar</button>
                    </form>
                    <a href="?delete=<?= htmlspecialchars($usuario['Rut']); ?>">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
