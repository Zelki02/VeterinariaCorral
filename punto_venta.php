<?php
// Incluye la conexión a la base de datos
include 'includes/db.php';

// Inicia la sesión para manejar el carrito
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Inicializa el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (!isset($_SESSION['descuento'])) {
    $_SESSION['descuento'] = 0; // Inicializa el descuento como 0
}

// Función para agregar un producto al carrito
function agregarAlCarrito($id_producto, $cantidad, $precio_temporal = null) {
    global $db;

    $query = "SELECT * FROM productos WHERE id_producto = '$id_producto'";
    $result = mysqli_query($db, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        // Si el producto no tiene precio, usa el precio temporal
        if (is_null($row['precio']) || $row['precio'] == 0) {
            if (is_null($precio_temporal) || $precio_temporal <= 0) {
                return false; // No se puede agregar sin precio válido
            }
            $row['precio'] = $precio_temporal; // Asignar precio temporal
        }

        // Verifica si el producto ya está en el carrito
        $producto_existente = false;
        foreach ($_SESSION['carrito'] as &$producto) {
            if($row['precio'] != $precio_temporal) {
            if ($producto['id_producto'] == $id_producto) {
                $producto['cantidad'] += $cantidad;
                $producto_existente = true;
                break;
            }
            }
        }

        if (!$producto_existente) {
            $row['cantidad'] = $cantidad;
            $_SESSION['carrito'][] = $row;
        }
        return true;
    }
    return false;
}


// Maneja la adición al carrito a través de la solicitud AJAX
if (isset($_POST['agregar_producto'])) {
    $id_producto = mysqli_real_escape_string($db, $_POST['id_producto']);
    $cantidad = (int)$_POST['cantidad'];
    $precio_temporal = isset($_POST['precio_temporal']) ? (float)$_POST['precio_temporal'] : null;

    if (agregarAlCarrito($id_producto, $cantidad, $precio_temporal)) {
        $totalCarrito = calcularTotalCarrito();
        $carritoHTML = obtenerHTMLCarrito();
        echo json_encode(['total' => number_format($totalCarrito, 2), 'carrito' => $carritoHTML]);
    } else {
        echo json_encode(['error' => 'No se pudo agregar el producto al carrito.']);
    }
    exit;
}


// Eliminar producto del carrito
if (isset($_POST['eliminar_producto'])) {
    $id_producto = mysqli_real_escape_string($db, $_POST['id_producto']);
    $_SESSION['carrito'] = array_filter($_SESSION['carrito'], function($producto) use ($id_producto) {
        return $producto['id_producto'] !== $id_producto;
    });

    // Devuelve el total y el HTML del carrito actualizado
    $totalCarrito = calcularTotalCarrito();
    $carritoHTML = obtenerHTMLCarrito();
    echo json_encode(['total' => number_format($totalCarrito, 2), 'carrito' => $carritoHTML]);
    exit; // Termina la ejecución aquí
}

// Nueva función para obtener el HTML del carrito
function obtenerHTMLCarrito() {
    if (empty($_SESSION['carrito'])) {
        return '<li class="list-group-item">El carrito está vacío.</li>';
    }

    $html = '';
    foreach ($_SESSION['carrito'] as $producto) {
        $html .= '<li class="list-group-item">'
            . $producto['nombre_producto'] . ' - $' . $producto['precio'] . ' (x' . $producto['cantidad'] . ')'
            . '<button class="btn btn-danger btn-sm float-right eliminar-producto" data-id="' . $producto['id_producto'] . '">Eliminar</button>'
            . '</li>';
    }
    return $html;
}

// Obtén la página actual desde la URL, por defecto será 1
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$productos_por_pagina = 10; // Número de productos por página

// Calcula el offset (desde dónde empezar a mostrar los productos)
$offset = ($pagina_actual - 1) * $productos_por_pagina;


// Consulta para obtener productos con paginación
$query = "SELECT id_producto, marca, nombre_producto, formato, precio 
          FROM productos 
          WHERE estatus = 'activo'
          LIMIT $offset, $productos_por_pagina";

$result = mysqli_query($db, $query);
if (!$result) {
    die("Error en la consulta: " . mysqli_error($db));
}

// Función para calcular el total del carrito
function calcularTotalCarrito() {
    $total = 0;
    $descuento = isset($_SESSION['descuento']) ? $_SESSION['descuento'] : 0; // Obtén el descuento de la sesión

    foreach ($_SESSION['carrito'] as $producto) {
        $total += $producto['precio'] * $producto['cantidad'];
    }

    if ($descuento > 0) {
        $total -= $total * ($descuento / 100);
    }

    return $total;
}


// Función para obtener los productos del carrito
function obtenerProductosCarrito() {
    return $_SESSION['carrito'];
}

// Maneja la solicitud para actualizar el carrito
if (isset($_POST['actualizar_carrito'])) {
    $totalCarrito = calcularTotalCarrito();
    $carritoHTML = obtenerHTMLCarrito();
    echo json_encode(['total' => number_format($totalCarrito, 2), 'carrito' => $carritoHTML]);
    exit; // Termina la ejecución aquí
}


$totalCarrito = calcularTotalCarrito(); // Calcula el total del carrito

if (isset($_POST['completar_compra'])) {
    $monto_pago = (float)$_POST['monto_pago'];
    $id_cliente = 1; // Cambia según el cliente actual
    $id_vendedor = 1; // Cambia según el vendedor actual
    $fecha_venta = date('Y-m-d H:i:s'); // Fecha y hora actual
    $total = calcularTotalCarrito(); // Calcula el total desde el carrito

    // Inserta la venta en la tabla ventas
    $query_venta = "INSERT INTO ventas (id_cliente, id_vendedor, fecha_venta, total) 
                    VALUES ('$id_cliente', '$id_vendedor', '$fecha_venta', '$total')";

    if (mysqli_query($db, $query_venta)) {
        $id_venta = mysqli_insert_id($db); // Obtiene el ID de la venta recién insertada

        // Obtén los productos del carrito
        $productos_carrito = obtenerProductosCarrito();

        // Inserta los productos en detalle_venta
        foreach ($productos_carrito as $producto) {
            $id_producto = $producto['id_producto'];
            $cantidad = $producto['cantidad'];
            $precio = $producto['precio'];
            $subtotal = $total;
        
            // Insertar la venta en detalle_venta
            $query_detalle = "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio, subtotal) 
                              VALUES ('$id_venta', '$id_producto', '$cantidad', '$precio', '$subtotal')";
            mysqli_query($db, $query_detalle);
        
            // Actualizar el stock de productos
            $query_actualizar_stock = "UPDATE productos 
                                       SET cantidad_stock = cantidad_stock - '$cantidad' 
                                       WHERE id_producto = '$id_producto'";
            mysqli_query($db, $query_actualizar_stock);
        
            // Obtener la fecha de ingreso más antigua para el producto
            $query_fecha_ingreso = "SELECT MIN(fecha_ingreso) AS fecha_ingreso_mas_antigua 
                                    FROM caducidades_productos 
                                    WHERE id_articulo = '$id_producto'";
            $resultado_fecha_ingreso = mysqli_query($db, $query_fecha_ingreso);
            $fila_fecha_ingreso = mysqli_fetch_assoc($resultado_fecha_ingreso);
            $fecha_ingreso = $fila_fecha_ingreso['fecha_ingreso_mas_antigua'];
        
            // Actualizar el lote de productos en caducidades_productos
            $query_actualizar_lote = "UPDATE caducidades_productos 
                                       SET lote_producto = lote_producto - '$cantidad' 
                                       WHERE id_articulo = '$id_producto' 
                                       AND fecha_ingreso = '$fecha_ingreso'";
            mysqli_query($db, $query_actualizar_lote);
        
            // Verificar si el lote_producto es 0, y eliminarlo si es necesario
            $query_verificar_lote = "SELECT lote_producto 
                                     FROM caducidades_productos 
                                     WHERE id_articulo = '$id_producto' 
                                     AND fecha_ingreso = '$fecha_ingreso'";
            $resultado_lote = mysqli_query($db, $query_verificar_lote);
            $fila_lote = mysqli_fetch_assoc($resultado_lote);
        
            if ($fila_lote['lote_producto'] <= 0) {
                // Eliminar el registro de caducidades_productos si el lote es 0 o menor
                $query_eliminar_lote = "DELETE FROM caducidades_productos 
                                        WHERE id_articulo = '$id_producto' 
                                        AND fecha_ingreso = '$fecha_ingreso'";
                mysqli_query($db, $query_eliminar_lote);
            }
        }
        
        
        // Limpia el carrito
        $_SESSION['carrito'] = [];
        unset($_SESSION['descuento']);

        echo json_encode(['status' => 'success', 'message' => 'Venta registrada correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al registrar la venta: ' . mysqli_error($db)]);
    }
    exit;
}

$metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : 'efectivo'; // 'efectivo' es el valor por defecto

// Maneja la solicitud para aplicar un descuento
if (isset($_POST['aplicar_descuento'])) {
    $descuento = (float)$_POST['descuento']; // Obtiene el descuento proporcionado
    $_SESSION['descuento'] = $descuento; // Almacena el descuento en la sesión

    // Devuelve el total actualizado
    $totalCarrito = calcularTotalCarrito();
    $carritoHTML = obtenerHTMLCarrito();
    echo json_encode(['total' => number_format($totalCarrito, 2), 'carrito' => $carritoHTML]);
    exit; // Termina la ejecución aquí
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
    /* Contenedor principal que utiliza Flexbox */
.contenedor {
    display: flex;               /* Usamos flexbox para que los elementos hijos se acomoden en una fila */
    justify-content: space-between;
    align-items: flex-start;     /* Alinea la tabla y el carrito en la parte superior */
    padding: 10px;
    gap: 20px;                   /* Espacio entre la tabla y el carrito */
    width: 100%;                 /* El contenedor ocupa todo el ancho de la página */
}

/* Estilo para la tabla */
.tabla-contenedor {
    width: 50%;                  /* La tabla ocupa el 50% del ancho total */
}

.table {
    width: 100%;                 /* La tabla ocupará el 100% de su contenedor */
    border-collapse: collapse;   /* Elimina los espacios entre las celdas */
}

.table th, .table td {
    padding: 4px 8px;            /* Espaciado interno en celdas */
    font-size: 14px;             /* Tamaño de la fuente */
    border: 1px solid #ccc;      /* Bordes alrededor de las celdas */
}

/* Resaltar la fila del producto al pasar el cursor */
#tabla-resultados tr:hover {
    background-color: #f0f0f0;
    cursor: pointer;
}

/* Estilos para el botón de agregar al carrito */
.agregar-al-carrito {
    display: none; /* Oculto por defecto */
    cursor: pointer;
}

#tabla-resultados tr:hover .agregar-al-carrito {
    display: inline-block; /* Mostrar al pasar el cursor sobre la fila */
}

/* Estilo para el carrito */
.carrito-contenedor {
    width: 40%;                  /* El carrito ocupa el 40% del ancho total */
    background-color: #f9f9f9;   /* Color de fondo del carrito */
    padding: 20px;               /* Espaciado interno */
    border: 1px solid #ccc;      /* Bordes para el carrito */
}

#carrito {
    width: 100%;                 /* El carrito ocupará el 100% de su contenedor */
}

#carrito h2 {
    font-size: 18px;
    margin-bottom: 10px;
}

.list-group-item {
    display: flex;               /* Usamos flex para poder alinear el contenido del carrito */
    justify-content: space-between;
    align-items: center;
    padding: 10px;
}

.list-group-item button {
    margin-left: 10px;
}

.list-group-item .eliminar-producto {
    background-color: #dc3545;    /* Color rojo para botón de eliminar */
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

.list-group-item .eliminar-producto:hover {
    background-color: #c82333;    /* Un rojo más oscuro al pasar el cursor */
}

.metodos-pago {
    display: flex;
    gap: 20px; /* Espacio entre los radio buttons */
    justify-content: flex-start; /* Alinea los radio buttons al inicio */
}

.metodos-pago label {
    display: inline-flex; /* Asegura que el radio button esté en línea con el texto */
    align-items: center; /* Alinea el texto y el radio button verticalmente */
}


    
</style>

</head>
<body>
<!-- Modal para completar la compra -->
<div class="modal fade" id="modal-completar-compra" tabindex="-1" role="dialog" aria-labelledby="modalCompletarCompraLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCompletarCompraLabel">Completar Compra</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Total de la compra: $<span id="total-modal"><?php echo number_format($totalCarrito, 2); ?></span></p>
                <label for="monto-pago">Ingresa el monto con el que esta pagando el cliente:</label>
                <input type="number" id="monto-pago" class="form-control" placeholder="Monto" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="confirmar-compra">Confirmar Compra</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal para ingresar precio temporal -->
<div class="modal fade" id="modal-precio" tabindex="-1" role="dialog" aria-labelledby="modalPrecioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPrecioLabel">Ingresar Precio Temporal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="producto-nombre"></p>
                <label for="precio-temporal">Precio:</label>
                <input type="number" id="precio-temporal" class="form-control" placeholder="Ingresa el precio" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="agregar-carrito">Agregar al Carrito</button>
            </div>
        </div>
    </div>
</div>

    <div class="container">
        <h1>Punto de Venta</h1>
        
        <!-- Métodos de pago -->
        <div class="metodos-pago">
        <label for="metodo_pago">Métodos de pago:</label><br>
        <label>
            <input type="radio" name="metodo_pago" value="efectivo" checked> Efectivo
        </label>
        <label>
            <input type="radio" name="metodo_pago" value="tarjeta"> Tarjeta
        </label>
        <label>
            <input type="radio" name="metodo_pago" value="transferencia"> Transferencia
        </label>
        </div>

        <!-- Barra de búsqueda -->
        <label for="busqueda">Buscar Producto (Nombre o Código de Barras):</label>
        <input type="text" id="busqueda" name="busqueda" class="form-control" placeholder="Escanea el código" autocomplete="off">
        <input type="text" id="codigo-barras" style="position:absolute; left:-9999px;" autofocus />

        <div class="contenedor">
    <!-- Tabla donde se mostrarán los productos -->
    <div class="tabla-contenedor">
        <table class="table table-bordered mt-3" id="resultados">
            <thead>
                <tr>
                    <th>ID Producto</th>
                    <th>Marca</th>
                    <th>Nombre</th>
                    <th>Formato</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody id="tabla-resultados">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr data-id="<?php echo $row['id_producto']; ?>">
                        <td><?php echo $row['id_producto']; ?></td>
                        <td><?php echo $row['marca']; ?></td>
                        <td><?php echo $row['nombre_producto']; ?></td>
                        <td><?php echo $row['formato']; ?></td>
                        <td><?php echo $row['precio']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>    
    </div>

    <!-- Carrito de compras -->
    <div class="carrito-contenedor">
        <ul id="carrito" class="list-group">
            <?php if (empty($_SESSION['carrito'])): ?>
                <li class="list-group-item">No se a añadido ningun producto.</li>
            <?php else: ?>
                <?php foreach ($_SESSION['carrito'] as $producto): ?>
                    <li class="list-group-item">
                        <?php echo $producto['nombre_producto']; ?> - $<?php echo $producto['precio']; ?> (x<?php echo isset($producto['cantidad']) ? $producto['cantidad'] : 1; ?>)
                        <button class="btn btn-danger btn-sm float-right eliminar-producto" data-id="<?php echo $producto['id_producto']; ?>">Eliminar</button>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>


        <!-- Muestra el total del carrito -->
        <h4>Total: $<span id="total-carrito"><?php echo number_format($totalCarrito, 2); ?></span></h4>
        <button id="aplicar-descuento" class="btn btn-primary">Aplicar Descuento</button>
        <button class="btn btn-success" id="completar-compra">Completar Compra</button>



    <script>
    $(document).ready(function(){
        // Búsqueda en tiempo real
        $('#busqueda').on('input', function() {
            var busqueda = $(this).val();
            $.ajax({
                url: 'buscar_producto.php',
                type: 'GET',
                data: { 
                    busqueda: busqueda, // Término de búsqueda
                    origen: 'punto_venta' // Parámetro adicional
                },
                success: function(response) {
                    $('#tabla-resultados').html(response);
                }
            });
        });

        $(document).ready(function() {
    // Función para agregar producto al carrito al hacer clic en la fila
    $(document).on('click', '#tabla-resultados tr', function () {
    var id_producto = $(this).data('id');
    if (id_producto !== undefined) {
        var cantidad = prompt("Ingresa la cantidad:");
        if (cantidad !== null && cantidad > 0) {
            // Verificar si se necesita un precio temporal
            var precio_temporal = null;
            var necesita_precio = $(this).find('td:nth-child(5)').text().trim() === '0' || 
                                  $(this).find('td:nth-child(5)').text().trim() === '';
            if (necesita_precio) {
                precio_temporal = prompt("El producto no tiene precio asignado. Ingresa un precio temporal:");
                if (precio_temporal === null || precio_temporal <= 0) {
                    alert("Debes ingresar un precio válido.");
                    return; // Detener si el precio es inválido
                }
            }

            // Enviar datos al servidor
            $.ajax({
                url: 'punto_venta.php', // Asegúrate de usar el archivo correcto
                type: 'POST',
                data: {
                    agregar_producto: true,
                    id_producto: id_producto,
                    cantidad: cantidad,
                    precio_temporal: precio_temporal
                },
                success: function (data) {
                    var response = JSON.parse(data);
                    $('#carrito').html(response.carrito);
                    $('#total-carrito').text(response.total);
                }
            });
        }
    }
});


    // Eliminar producto del carrito
    $(document).on('click', '.eliminar-producto', function() {
        var id_producto = $(this).data('id');
        $.ajax({
            url: 'punto_venta.php', // La URL donde se maneja la eliminación del producto
            type: 'POST',
            data: { eliminar_producto: true, id_producto: id_producto },
            success: function(data) {
                var response = JSON.parse(data);
                $('#carrito').html(response.carrito); // Actualiza el HTML del carrito
                $('#total-carrito').text(response.total); // Actualiza el total del carrito
            }
        });
    });
});
    });

    

    $(document).ready(function() {
    // Enfoca el campo de código de barras al cargar la página
    $('#codigo-barras').focus();

    // Evento para capturar la entrada del campo de código de barras
    $('#codigo-barras').on('keypress', function(e) {
        if (e.which === 13) { // 13 es la tecla Enter
            var codigo = $(this).val(); // Obtén el código escaneado
            $(this).val(''); // Limpia el campo de entrada

            // Realiza la solicitud AJAX para agregar el producto al carrito
            $.ajax({
                url: 'punto_venta.php', // Archivo que maneja la solicitud
                type: 'POST',
                data: { agregar_producto: true, codigo_barras: codigo },
                success: function(data) {
                    var response = JSON.parse(data);
                    $('#carrito').html(response.carrito);
                    $('#total-carrito').text(response.total);
                }
            });
        }
    });
});

$(document).on('click', '#completar-compra', function() {
    $('#total-modal').text($('#total-carrito').text()); // Muestra el total en el modal
    $('#modal-completar-compra').modal('show'); // Abre el modal
});

// Maneja la confirmación de la compra
$(document).on('click', '#confirmar-compra', function() {
    var monto_pago = parseFloat($('#monto-pago').val());
    var total_compra = parseFloat($('#total-carrito').text());

    if (monto_pago >= total_compra) {
        var cambio = monto_pago - total_compra; // Cálculo del cambio

        // Realiza la compra, actualiza el stock y cierra el modal
        $.ajax({
            url: 'punto_venta.php', // URL donde se procesará la compra
            type: 'POST',
            data: { completar_compra: true, monto_pago: monto_pago },
            success: function(data) {
                // Aquí puedes procesar la respuesta del servidor
                alert(`Compra completada con éxito.\nTotal: $${total_compra.toFixed(2)}\nMonto recibido: $${monto_pago.toFixed(2)}\nCambio: $${cambio.toFixed(2)}`);
                $('#modal-completar-compra').modal('hide');
                
                // Limpiar el carrito después de completar la compra
                $('#carrito').html('<li class="list-group-item">No se ha añadido ningún producto.</li>');
                $('#total-carrito').text('0.00');
            },
            error: function() {
                alert('Error al completar la compra. Inténtalo de nuevo.');
            }
        });
    } else {
        alert('El monto ingresado es menor al total de la compra.');
    }
});


document.getElementById('aplicar-descuento').addEventListener('click', function () {
    // Solicita al usuario el descuento
    const descuento = prompt("Ingresa el porcentaje de descuento (ejemplo: 10 para 10%):");
    
    if (descuento !== null && !isNaN(descuento) && descuento >= 0 && descuento <= 100) {
        // Envía el descuento al servidor
        $.ajax({
            url: 'punto_venta.php', // Cambia por el nombre de tu archivo
            type: 'POST',
            data: {
                aplicar_descuento: true,
                descuento: descuento
            },
            dataType: 'json',
            success: function (response) {
                // Actualiza el total y el carrito en la página
                $('#total-carrito').text(`${response.total}`);
                $('#carrito').html(response.carrito);
            },
            error: function () {
                alert('Hubo un error al aplicar el descuento.');
            }
        });
    } else {
        alert("Por favor, ingresa un porcentaje de descuento válido (0-100).");
    }
});

document.addEventListener('DOMContentLoaded', () => {
  const montoPagoInput = document.getElementById('monto-pago');
  const confirmarPagoBtn = document.getElementById('confirmar-compra');

  // Escuchar el evento 'keypress' en el campo de texto
  montoPagoInput.addEventListener('keypress', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault(); // Prevenir comportamiento por defecto

      // Validar el monto ingresado
      const monto = parseFloat(montoPagoInput.value);
      if (isNaN(monto) || monto <= 0) {
        alert('Por favor, ingresa un monto válido.');
        return;
      }

      // Simular la acción del botón "Confirmar"
      confirmarPagoBtn.click();
    }
  });

  confirmarPagoBtn.addEventListener('click', () => {
    const monto = parseFloat(montoPagoInput.value);

    if (isNaN(monto) || monto <= 0) {
      alert('Por favor, ingresa un monto válido.');
      return;
    }

    completarCompra(monto);

    // Cerrar el modal automáticamente
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalPago'));
    modal.hide();
  });
});

// Función para procesar la compra
function completarCompra(monto) {
  console.log(`Procesando compra con monto pagado: ${monto}`);
  alert(`Compra completada con éxito. Cambio: ${monto - totalCompra}`);
}

// Limpia el campo de texto al abrir el modal
$('#modal-completar-compra').on('shown.bs.modal', function () {
    $('#monto-pago').val(''); // Vaciar el campo de texto
});
    </script>
</body>
</html>