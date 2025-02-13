<?php
include 'views/header.php';
require_once 'includes/db.php'; // Asegúrate de que la conexión a la base de datos esté bien configurada
require 'vendor/autoload.php'; // Autoload para PhpSpreadsheet


// Capturar la búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Consulta a la base de datos
$query = "SELECT * FROM productos WHERE nombre_producto LIKE ?";
$stmt = $db->prepare($query);
$busqueda_param = '%' . $busqueda . '%'; // Para buscar coincidencias parciales
$stmt->bind_param("s", $busqueda_param);
$stmt->execute();
$result = $stmt->get_result();

// Parámetros para la paginación
$productos_por_pagina = 100;  // Mostrar 50 productos por página
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página actual
$inicio = ($pagina_actual - 1) * $productos_por_pagina; // Calcular el inicio

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

//Obtener Total de Articulos
$result = $db->query("SELECT COUNT(*) AS total_productos FROM productos");
$row = $result->fetch_assoc();
$total_productos = $row['total_productos'];

// Cargar inventario desde la base de datos
// Obtener productos con límite
$query = "SELECT * FROM productos LIMIT $inicio, $productos_por_pagina";
$result = $db->query($query);

// Contar el número total de productos
$total_productos_query = "SELECT COUNT(*) as total FROM productos";
$total_productos_result = $db->query($total_productos_query);
$total_productos = $total_productos_result->fetch_assoc()['total'];
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Agregar nuevo producto
if (isset($_POST['agregar'])) {
    // Datos para la tabla productos
    $codigo_barras = $_POST['codigo_barras'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];
    $estatus = $_POST['estatus'];
    $categoria = $_POST['categoria'];
    $marca = $_POST['marca'];
    $formato = $_POST['formato'];
    $iva = $_POST['iva'];
    
    // Datos para la tabla caducidades_productos
    $fecha_caducidad = $_POST['fecha_caducidad'] . "-01"; // Día fijo como 1
    $lote_producto = $_POST['cantidad'];

    $db->begin_transaction(); // Inicia la transacción

    try {
        // Insertar en la tabla productos
        $stmt1 = $db->prepare("INSERT INTO productos (id_producto, nombre_producto, descripcion, precio, cantidad_stock, estatus, categoria, marca, formato, iva) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssdisdssd", $codigo_barras, $nombre, $descripcion, $precio, $cantidad, $estatus, $categoria, $marca, $formato, $iva);
        $stmt1->execute();

        // Insertar en la tabla caducidades_productos
        $stmt2 = $db->prepare("INSERT INTO caducidades_productos (id_articulo, fecha_caducidad, lote_producto) 
                               VALUES (?, ?, ?)");
        $stmt2->bind_param("sss", $codigo_barras, $fecha_caducidad, $lote_producto);
        $stmt2->execute();

        $db->commit(); // Confirmar transacción
        echo "<script type='text/javascript'>
                window.location.href = 'inventario.php';
              </script>";
        exit;

    } catch (Exception $e) {
        $db->rollback(); // Deshacer transacción en caso de error
        echo "Error al agregar el producto: " . $e->getMessage();
    }
}



// Eliminar producto
if (isset($_GET['eliminar'])) {
    $codigo_barras = $_GET['eliminar'];
    $stmt = $db->prepare("DELETE FROM productos WHERE id_producto = ?");
    $stmt->bind_param("s", $codigo_barras);
    
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
        window.location.href = 'inventario.php';
      </script>";
exit;  // Detener la ejecución del script
    } else {
        echo "Error al eliminar el producto: " . $stmt->error;
    }
}

// Editar producto
if (isset($_POST['editar'])) {
    $codigo_barras = $_POST['id_producto'];
    $nombre = $_POST['nombre_producto'];
    $categoria = $_POST['categoria'];
    $marca = $_POST['marca'];
    $formato = $_POST['formato'];
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];
    $iva = $_POST['iva'];
    $estatus = $_POST['estatus'];
    $cantidad = $_POST['cantidad'];
    $fecha_caducidad = $_POST['fecha_caducidad'] ?: "9999-12-31"; // Valor por defecto si está vacío
    $fecha_actualizacion = date('Y-m-d H:i:s');
    $fecha_ingreso = date('Y-m-d H:i:s'); // Fecha actual para el registro del lote

    // Obtener el stock actual antes de la edición
    $query_stock = $db->prepare("SELECT cantidad_stock FROM productos WHERE id_producto = ?");
    $query_stock->bind_param("s", $codigo_barras);
    $query_stock->execute();
    $result = $query_stock->get_result();
    $producto_actual = $result->fetch_assoc();
    $stock_anterior = $producto_actual['cantidad_stock'];

    // Calcular la diferencia en el stock
    $cantidad_cambiada = $cantidad - $stock_anterior;

    // Actualizar producto en la tabla 'productos'
    $stmt = $db->prepare("UPDATE productos SET categoria=?, marca=?, nombre_producto=?, formato=?, precio=?, descripcion=?, iva=?, estatus=?, cantidad_stock=? WHERE id_producto=?");
    $stmt->bind_param("ssssdsisis", $categoria, $marca, $nombre, $formato, $precio, $descripcion, $iva, $estatus, $cantidad, $codigo_barras);

    if ($stmt->execute()) {
        $nuevo_stock = $cantidad;

        // Insertar el registro en 'control_inventario'
        $stmt_control = $db->prepare("INSERT INTO control_inventario (id_producto, fecha_actualizacion, cantidad_cambiada, nuevo_stock) VALUES (?, ?, ?, ?)");
        $stmt_control->bind_param("ssii", $codigo_barras, $fecha_actualizacion, $cantidad_cambiada, $nuevo_stock);

        if (!$stmt_control->execute()) {
            echo "Error al registrar en control_inventario: " . $stmt_control->error;
        }

        // Si se añadió stock, registrar el nuevo lote y su caducidad
        if ($fecha_caducidad != "9999-12-31") {
            $fecha_caducidad = $_POST['fecha_caducidad']. "-01";
            $stmt_caducidad = $db->prepare("INSERT INTO caducidades_productos (id_articulo, fecha_caducidad, lote_producto, fecha_ingreso) VALUES (?, ?, ?, ?)");
            $stmt_caducidad->bind_param("ssss", $codigo_barras, $fecha_caducidad, $cantidad_cambiada, $fecha_ingreso);

            if (!$stmt_caducidad->execute()) {
                echo "Error al registrar en caducidades_productos: " . $stmt_caducidad->error;
            }
        }

        // Redirigir al inventario
        echo "<script type='text/javascript'>
        window.location.href = 'inventario.php';
        </script>";
        exit; // Detener la ejecución del script
    } else {
        echo "Error al editar el producto: " . $stmt->error;
    }
}




//Carga Masiva
if (isset($_POST['carga_masiva'])) {
    $file_mimes = array('application/vnd.ms-excel', 'text/xls', 'text/xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    if (isset($_FILES['file']['name']) && in_array($_FILES['file']['type'], $file_mimes)) {
        $arr_file = explode('.', $_FILES['file']['name']);
        $extension = end($arr_file);

        if ('xlsx' == $extension) {
            $reader = IOFactory::createReader('Xlsx');
        } else {
            $reader = IOFactory::createReader('Xls');
        }

        $spreadsheet = $reader->load($_FILES['file']['tmp_name']);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        // Eliminar todos los datos actuales de la tabla productos
        $db->query("DELETE FROM productos");

        // Arreglo para almacenar IDs duplicados
        $duplicados = [];

        // Iterar sobre los datos empezando desde la fila 2 (índice 1)
        for ($i = 1; $i < count($sheetData); $i++) {
            $row = $sheetData[$i];

            $codigo_barras = $row[0];    // id_producto
            $categoria = $row[1];
            $marca = $row[2];    
            $nombre = $row[3];           // nombre_producto
            $formato = $row[4];  
            $precio = !empty($row[5]) ? (float)$row[5] : NULL;  // Validar que el precio no esté vacío
            $descripcion = $row[6];      // descripcion
            $iva = !empty($row[7]) ? (float)$row[7] : NULL;  // Validar que el IVA no esté vacío
            $estatus = $row[8];
            $cantidad = !empty($row[9]) ? (int)$row[9] : 0;  // Si cantidad es nula, poner 0

            // Verifica que no haya valores nulos importantes antes de insertar
            if (empty($codigo_barras) || empty($nombre)) {
                continue; // Omite filas inválidas
            }

            // Verificar si el ID ya existe en la base de datos
            $check_stmt = $db->prepare("SELECT id_producto FROM productos WHERE id_producto = ?");
            $check_stmt->bind_param("s", $codigo_barras);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                // Si el ID ya existe, lo añadimos a los duplicados y continuamos con la siguiente fila
                $duplicados[] = $codigo_barras;
                continue;
            }

            // Insertar el producto solo si no es duplicado
            $stmt = $db->prepare("INSERT INTO productos (id_producto, categoria, marca, nombre_producto, formato, precio, descripcion, iva, estatus, cantidad_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdsisi", $codigo_barras, $categoria, $marca, $nombre, $formato, $precio, $descripcion, $iva, $estatus, $cantidad);
            $stmt->execute();
        }

        // Verificar si hubo IDs duplicados y mostrarlos
        if (!empty($duplicados)) {
            // Pasamos los IDs duplicados a JavaScript para que los muestre en el modal
            $duplicados_str = implode(", ", $duplicados);
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Insertar los IDs duplicados en el modal
                    document.getElementById('duplicados-list').innerText = '$duplicados_str';
                    
                    // Mostrar el modal de Bootstrap
                    var duplicadosModal = new bootstrap.Modal(document.getElementById('duplicadosModal'));
                    duplicadosModal.show();
                });
            </script>";
        }      else {
            echo "<script type='text/javascript'>
        window.location.href = 'inventario.php';
      </script>";
exit;  // Detener la ejecución del script

        }  
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>
    <link rel="stylesheet" href="css/estilos.css">
    <!-- Incluir Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
/* Estilos generales */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 0;
}

h1, h2 {
    text-align: center;
    color: #5a5a5a;
}

.container {
    width: 80%;
    margin: 20px auto;
    background-color: white;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Estilo de la tabla */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    table-layout: fixed; /* Fija el ancho de las columnas */
}

table, th, td {
    border: 1px solid #ddd;
    padding: 5px;
    text-align: center;
    font-size: 12px;
}

th, td {
    padding: 8px;
    text-align: left;
}

th {
    background-color: #d9a066;
    color: white;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Paginación */
#main-nav ul {
    list-style: none;
    display: flex;
    justify-content: center;
    padding: 0;
}

#main-nav ul li {
    margin: 0 5px;
}

#main-nav ul li a {
    text-decoration: none;
    color: #007bff;
    padding: 8px 16px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

#main-nav ul li a:hover {
    background-color: #007bff;
    color: white;
}

/* Formulario */
form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
}

form label {
    flex: 1 1 100%;
    margin-bottom: 10px;
}

form input, form button {
    padding: 10px;
    width: 100%;
    margin: 5px 0;
    border-radius: 4px;
    border: 1px solid #ddd;
}

form button {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #0056b3;
}

/* Botones */
.btn {
    padding: 6px 13px;
    font-size: 12px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}

/* Adaptaciones responsivas */
@media (max-width: 1024px) { /* Tablets y pantallas medianas */
    .container {
        width: 90%;
    }
    form {
        flex-direction: column; /* Apila el formulario */
    }
}

@media (max-width: 768px) { /* Móviles */
    .container {
        width: 95%;
        padding: 10px;
    }
    table {
        display: block;
        overflow-x: auto; /* Tabla desplazable en móviles */
        white-space: nowrap;
    }
    th, td {
        font-size: 11px; /* Reducir el texto */
    }
    form {
        flex-direction: column; /* Asegura que el formulario esté en columna */
    }
    .btn {
        font-size: 10px; /* Ajustar botones en móviles */
        width: 100%; /* Botones de ancho completo */
    }
}

</style>
</head>
<body>
<!-- Modal de Duplicados -->
<div class="modal fade" id="duplicadosModal" tabindex="-1" aria-labelledby="duplicadosModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="duplicadosModalLabel">IDs Duplicados Encontrados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Se encontraron los siguientes IDs duplicados en tu archivo Excel:porfavor revisa que los Codigo de barra no se repitan<span id="duplicados-list"></span>. <br><br>
        ¿Deseas continuar con los registros válidos?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="inventario.php" class="btn btn-primary">Continuar</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Edición -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-editar" method="POST" action="inventario.php">
                    <input type="hidden" name="id_producto" id="id_producto" value="">
                    <div class="mb-3">
                        <label for="nombre_producto" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" required>
                    </div>
                    <div class="mb-3">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" class="form-control" id="marca" name="marca">
                    </div>
                    <div class="mb-3">
                        <label for="formato" class="form-label">Formato</label>
                        <input type="text" class="form-control" id="formato" name="formato" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" class="form-control" id="precio" name="precio" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="iva" class="form-label">IVA</label>
                        <input type="number" class="form-control" id="iva" name="iva" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="estatus" class="form-label">Estatus</label>
                        <select class="form-select" id="estatus" name="estatus" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad">
                    </div>
                    <!-- Nuevos campos para la gestión de lotes -->
                    <div class="mb-3">
                        <label for="fecha_caducidad" class="form-label">Fecha de Caducidad</label>
                        <input type="month" class="form-control" id="fecha_caducidad" name="fecha_caducidad">
                    </div>
                    <button type="submit" name="editar" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal Carga Masiva-->
<div class="modal fade" id="cargaMasivaModal" tabindex="-1" aria-labelledby="cargaMasivaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cargaMasivaModalLabel">Carga Masiva de Archivos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="fileInput" class="form-label">Selecciona el archivo a subir</label>
            <input class="form-control" type="file" id="fileInput" name="file" accept=".xls,.xlsx" required>
          </div>
          <div class="text-end">
            <button type="submit" class="btn btn-pr imary" name="carga_masiva">Subir Archivo</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Model Agregar producto-->
<div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-labelledby="agregarProductoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="agregarProductoModalLabel">Agregar Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6">
                <!-- Primera columna -->
                <div class="mb-3">
                  <label for="codigo_barras" class="form-label">Código de Barras</label>
                  <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" required>
                </div>
                <div class="mb-3">
                  <label for="categoria" class="form-label">Categoría</label>
                  <input type="text" class="form-control" id="categoria" name="categoria" required>
                </div>
                <div class="mb-3">
                  <label for="marca" class="form-label">Marca</label>
                  <input type="text" class="form-control" id="marca" name="marca">
                </div>
                <div class="mb-3">
                  <label for="nombre" class="form-label">Nombre</label>
                  <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="mb-3">
                  <label for="formato" class="form-label">Formato</label>
                  <input type="text" class="form-control" id="formato" name="formato" required>
                </div>
              </div>
              <div class="col-md-6">
                <!-- Segunda columna -->
                <div class="mb-3">
                  <label for="precio" class="form-label">Precio</label>
                  <input type="number" class="form-control" id="precio" name="precio" step="0.01">
                </div>
                <div class="mb-3">
                  <label for="descripcion" class="form-label">Descripción</label>
                  <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                </div>
                <div class="mb-3">
                  <label for="iva" class="form-label">IVA</label>
                  <input type="number" class="form-control" id="iva" name="iva" step="0.01">
                </div>
                <div class="mb-3">
                  <label for="estatus" class="form-label">Estatus</label>
                  <select class="form-select" id="estatus" name="estatus" required>
                    <option value="Activo">Activo</option>
                    <option value="No Activo">No Activo</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="cantidad" class="form-label">Cantidad</label>
                  <input type="number" class="form-control" id="cantidad" name="cantidad" required>
                </div>
                <div class="mb-3">
  <label for="fecha_caducidad" class="form-label">Fecha de Caducidad</label>
  <input type="month" class="form-control" id="fecha_caducidad" name="fecha_caducidad" required>
</div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
        <button type="submit" class="btn btn-primary" name="agregar">Guardar Producto</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="container">
    <h1>Gestión de Inventario</h1>

    <!-- Mostrar el total de productos -->
    <h3>Total de productos en inventario: <?php echo $total_productos; ?>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cargaMasivaModal">
        Carga Masiva
    </button>
    <!-- Botón para abrir el modal -->
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
        Agregar Producto
    </button>

<!-- Formulario de búsqueda -->
<input type="text" id="busqueda" name="busqueda" placeholder="Buscar por nombre de producto" onkeyup="buscarProducto()" class="form-control">

</form>

    </h3>

<!-- Tabla de productos -->
<table>
    <thead>
        <tr>
            <th>Código de Barras</th>
            <th>Categoria</th>
            <th>Marca</th>
            <th>Nombre</th>
            <th>Formato</th>
            <th>Precio</th>
            <th>Descripción</th>
            <th>IVA</th>
            <th>Estatus</th>
            <th>Cantidad</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="tabla-inventario">
        <!-- Aquí se insertarán los resultados de la búsqueda -->
        <?php
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id_producto'] . "</td>";
            echo "<td>" . $row['categoria'] . "</td>";
            echo "<td>" . $row['marca'] . "</td>";
            echo "<td>" . $row['nombre_producto'] . "</td>";
            echo "<td>" . $row['formato'] . "</td>";
            echo "<td>" . $row['precio'] . "</td>";
            echo "<td>" . $row['descripcion'] . "</td>";
            echo "<td>" . $row['iva'] . "</td>";
            echo "<td>" . $row['estatus'] . "</td>";
            echo "<td>" . $row['cantidad_stock'] . "</td>";
            echo '<td class="actions">';  //Clase para aplicar los estilos a esta columna -->
            echo '<button type="button" 
                    class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editarModal" 
                    data-id="' . $row['id_producto'] . '" 
                    data-categoria="' . $row['categoria'] . '"
                    data-marca="' . $row['marca'] . '"
                    data-nombre="' . $row['nombre_producto'] . '"
                    data-formato="' . $row['formato'] . '"
                    data-precio="' . $row['precio'] . '"
                    data-descripcion="' . $row['descripcion'] . '"
                    data-iva="' . $row['iva'] . '"
                    data-estatus="' . $row['estatus'] . '"
                    data-cantidad="' . $row['cantidad_stock'] . '">
                Editar
              </button>';
            echo ' <a href="inventario.php?eliminar=' . $row['id_producto'] . '" class="btn btn-danger">Eliminar</a>';
            echo '</td>';
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

    <!-- Enlaces de Paginación -->
    <nav id="main-nav">
    <ul>
        
    <?php
    // Definir el rango de páginas visibles
    $rango = 3;
    $inicio = max(1, $pagina_actual - $rango);
    $fin = min($total_paginas, $pagina_actual + $rango);

    // Obtener el valor de búsqueda si existe
    $busqueda = isset($_POST['query']) ? $_POST['query'] : '';

    // Botón anterior
    if ($pagina_actual > 1) {
        echo '<li><a href="inventario.php?pagina=' . ($pagina_actual - 1) . '&query=' . $busqueda . '">⬅</a></li>';
    }

    // Mostrar "1 ..." si no estamos cerca de la primera página
    if ($inicio > 1) {
        echo '<li><a href="inventario.php?pagina=1&query=' . $busqueda . '">1</a></li>';
        if ($inicio > 2) {
            echo '<li>...</li>';
        }
    }

    // Páginas dentro del rango
    for ($i = $inicio; $i <= $fin; $i++) {
        if ($i == $pagina_actual) {
            echo '<li><strong>' . $i . '</strong></li>'; // Página actual en negrita
        } else {
            echo '<li><a href="inventario.php?pagina=' . $i . '&query=' . $busqueda . '">' . $i . '</a></li>';
        }
    }

    // Mostrar "... última página" si no estamos cerca de la última página
    if ($fin < $total_paginas) {
        if ($fin < $total_paginas - 1) {
            echo '<li>...</li>';
        }
        echo '<li><a href="inventario.php?pagina=' . $total_paginas . '&query=' . $busqueda . '">' . $total_paginas . '</a></li>';
    }

    // Botón siguiente
    if ($pagina_actual < $total_paginas) {
        echo '<li><a href="inventario.php?pagina=' . ($pagina_actual + 1) . '&query=' . $busqueda . '">⮕</a></li>';
    }
    ?>
    </ul>
</nav>


</div>
        <!-- Incluir Bootstrap JS y sus dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function cargarDatosProducto(id, nombre, categoria, marca, formato, precio, descripcion, iva, estatus, cantidad) {
        document.getElementById('id_producto').value = id;
        document.getElementById('nombre_producto').value = nombre;
        document.getElementById('categoria').value = categoria;
        document.getElementById('marca').value = marca;
        document.getElementById('formato').value = formato;
        document.getElementById('precio').value = precio;
        document.getElementById('descripcion').value = descripcion;
        document.getElementById('iva').value = iva;
        document.getElementById('estatus').value = estatus;
        document.getElementById('cantidad').value = cantidad;
    }



    function buscarProducto() {
    var query = document.getElementById('busqueda').value;

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Reemplaza el contenido de la tabla con los resultados de la búsqueda
            document.getElementById('tabla-inventario').innerHTML = this.responseText;
        }
    };

    xhr.open("GET", "buscar_producto.php?busqueda=" + query, true);
    xhr.send();
}

document.addEventListener('DOMContentLoaded', function () {
        // Capturar el evento cuando se muestra el modal
        const editarModal = document.getElementById('editarModal');
        editarModal.addEventListener('show.bs.modal', function (event) {
            // Botón que disparó el modal
            const button = event.relatedTarget;

            // Extraer datos del botón
            const idProducto = button.getAttribute('data-id');
            const categoria = button.getAttribute('data-categoria');
            const marca = button.getAttribute('data-marca');
            const nombre = button.getAttribute('data-nombre');
            const formato = button.getAttribute('data-formato');
            const precio = button.getAttribute('data-precio');
            const descripcion = button.getAttribute('data-descripcion');
            const iva = button.getAttribute('data-iva');
            const estatus = button.getAttribute('data-estatus');
            const cantidad = button.getAttribute('data-cantidad');

            // Llenar los campos del modal
            editarModal.querySelector('#id_producto').value = idProducto;
            editarModal.querySelector('#categoria').value = categoria;
            editarModal.querySelector('#marca').value = marca;
            editarModal.querySelector('#nombre_producto').value = nombre;
            editarModal.querySelector('#formato').value = formato;
            editarModal.querySelector('#precio').value = precio;
            editarModal.querySelector('#descripcion').value = descripcion;
            editarModal.querySelector('#iva').value = iva;
            editarModal.querySelector('#estatus').value = estatus;
            editarModal.querySelector('#cantidad').value = cantidad;
        });
    });
</script>
<?php
include 'views/footer.php';
?>
</body>
</html>