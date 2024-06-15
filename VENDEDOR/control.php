<?php
session_start();
if (!isset($_SESSION['Tipo']) || $_SESSION['Tipo'] !== 'Vendedor') {
    header('Location: /index.php');
    exit();
}

include '../conexion.php';

// Función para obtener repuestos filtrados
function getRepuestosFiltrados($pdo, $filtro) {
    $sql = "SELECT * FROM repuestos WHERE 
            NombreRepuesto LIKE :filtro OR 
            PrecioUnitario LIKE :filtro OR 
            CantidadStock LIKE :filtro OR 
            Proveedor LIKE :filtro";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':filtro', '%' . $filtro . '%', PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener todos los repuestos (sin filtrar)
function getRepuestos($pdo) {
    $stmt = $pdo->query("SELECT * FROM repuestos");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Insertar un nuevo repuesto
if (isset($_POST['add'])) {
    $NombreRepuesto = $_POST['NombreRepuesto'];
    $PrecioUnitario = $_POST['PrecioUnitario'];
    $CantidadStock = $_POST['CantidadStock'];
    $Proveedor = $_POST['Proveedor'];

    $stmt = $pdo->prepare("INSERT INTO repuestos (NombreRepuesto, PrecioUnitario, CantidadStock, Proveedor) VALUES (?, ?, ?, ?)");
    $stmt->execute([$NombreRepuesto, $PrecioUnitario, $CantidadStock, $Proveedor]);

    header("Location: control.php");
    exit();
}

// Eliminar un repuesto
if (isset($_GET['delete'])) {
    $RepuestoID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM repuestos WHERE RepuestoID = ?");
    $stmt->execute([$RepuestoID]);

    header("Location: control.php");
    exit();
}

// Formulario para actualizar
if (isset($_POST['update'])) {
    $RepuestoID = $_POST['RepuestoID'];
    $NombreRepuesto = $_POST['NombreRepuesto_' . $RepuestoID];
    $PrecioUnitario = $_POST['PrecioUnitario_' . $RepuestoID];
    $CantidadStock = $_POST['CantidadStock_' . $RepuestoID];
    $Proveedor = $_POST['Proveedor_' . $RepuestoID];

    $stmt = $pdo->prepare("UPDATE repuestos SET NombreRepuesto = ?, PrecioUnitario = ?, CantidadStock = ?, Proveedor = ? WHERE RepuestoID = ?");
    $stmt->execute([$NombreRepuesto, $PrecioUnitario, $CantidadStock, $Proveedor, $RepuestoID]);

    header("Location: control.php");
    exit();
}

// Determinar si hay un filtro aplicado
$filtro = '';
if (isset($_POST['buscar'])) {
    $filtro = $_POST['filtro'];
    $repuestos = getRepuestosFiltrados($pdo, $filtro);
} else {
    $repuestos = getRepuestos($pdo);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Repuestos</title>
    <link rel="stylesheet" href="css/style2.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1, h2 {
            color: #444;
        }

        form {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }

        form input[type="text"], form input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
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
            margin-top: 20px;
            display: block; /* Para que el enlace ocupe todo el ancho disponible */
            text-align: center; /* Centrar el texto del enlace */
        }
    </style>
</head>
<body>
    <h1>Gestionar Repuestos</h1>
    <form action="control.php" method="post">
        <input type="text" name="NombreRepuesto" placeholder="Nombre del repuesto" required>
        <input type="number" name="PrecioUnitario" placeholder="Precio unitario" required>
        <input type="number" name="CantidadStock" placeholder="Cantidad Stock" required>
        <input type="text" name="Proveedor" placeholder="Proveedor" required>
        <button type="submit" name="add">Agregar Repuesto</button>
    </form>
    <hr>
    <!-- Formulario para filtrar -->
    <form action="control.php" method="post">
        <input type="text" name="filtro" placeholder="Buscar por nombre, precio, stock o proveedor">
        <button type="submit" name="buscar">Buscar</button>
        <a class="login-button" href="../login.php"> SALIR </a>
    </form>
    <h2>Lista de Repuestos</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Precio Unitario</th>
                <th>Cantidad Stock</th>
                <th>Proveedor</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($repuestos as $repuesto): ?>
            <tr>
                <td><?= $repuesto['RepuestoID']; ?></td>
                <td><?= $repuesto['NombreRepuesto']; ?></td>
                <td><?= $repuesto['PrecioUnitario']; ?></td>
                <td><?= $repuesto['CantidadStock']; ?></td>
                <td><?= $repuesto['Proveedor']; ?></td>
                <td>
                    <form action="control.php" method="post">
                        <input type="hidden" name="RepuestoID" value="<?= $repuesto['RepuestoID']; ?>">
                        <input type="text" name="NombreRepuesto_<?= $repuesto['RepuestoID']; ?>" value="<?= $repuesto['NombreRepuesto']; ?>">
                        <input type="number" name="PrecioUnitario_<?= $repuesto['RepuestoID']; ?>" value="<?= $repuesto['PrecioUnitario']; ?>">
                        <input type="number" name="CantidadStock_<?= $repuesto['RepuestoID']; ?>" value="<?= $repuesto['CantidadStock']; ?>">
                        <input type="text" name="Proveedor_<?= $repuesto['RepuestoID']; ?>" value="<?= $repuesto['Proveedor']; ?>">
                        <button type="submit" name="update">Actualizar</button>
                    </form>
                    <a href="?delete=<?= $repuesto['RepuestoID']; ?>">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
