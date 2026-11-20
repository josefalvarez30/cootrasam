<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../conexion.php';

$mensaje = "";

// ===== GUARDAR (agregar nuevo o editar existente) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $origen = $_POST['origen'];
    $tipo_dia = $_POST['tipo_dia'];
    $primera_salida = $_POST['primera_salida'];
    $ultima_salida = $_POST['ultima_salida'];
    $frecuencia = $_POST['frecuencia'];

    if ($id === '') {
        // Antes de crear uno nuevo, verificamos que no exista ya
        // un horario para ese mismo origen + tipo de día
        $check = $conexion->prepare("SELECT id FROM horarios WHERE origen = ? AND tipo_dia = ?");
        $check->bind_param("ss", $origen, $tipo_dia);
        $check->execute();
        $existe = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existe) {
            $id_existente = $existe['id'];
            $mensaje = " Ya existe un horario para " . htmlspecialchars($origen) . " / " . htmlspecialchars($tipo_dia) . ". No se creó uno nuevo — edita el que ya existe en la tabla de abajo.";
        } else {
            $sql = "INSERT INTO horarios (origen, tipo_dia, primera_salida, ultima_salida, frecuencia) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssss", $origen, $tipo_dia, $primera_salida, $ultima_salida, $frecuencia);
            $stmt->execute();
            $stmt->close();
            $mensaje = "Horario agregado correctamente.";
        }
    } else {
        // Es edición
        $sql = "UPDATE horarios SET origen=?, tipo_dia=?, primera_salida=?, ultima_salida=?, frecuencia=? WHERE id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssi", $origen, $tipo_dia, $primera_salida, $ultima_salida, $frecuencia, $id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Horario actualizado correctamente.";
    }
}

// ===== ELIMINAR =====
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM horarios WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Horario eliminado.";
}

// Si venimos a editar, cargar ese registro
// Nota: si llega ?nuevo=1 en la URL, IGNORAMOS cualquier ?editar=
// para asegurarnos de que el formulario quede limpio sí o sí.
$editando = null;
if (isset($_GET['editar']) && !isset($_GET['nuevo'])) {
    $id = $_GET['editar'];
    $stmt = $conexion->prepare("SELECT * FROM horarios WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editando = $res->fetch_assoc();
    $stmt->close();
}

//  Traer todos los horarios para la tabla
$horarios = $conexion->query("SELECT * FROM horarios ORDER BY FIELD(tipo_dia,'semana','sabado','domingo'), origen");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Horarios - Panel Admin Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
  body { background: #f7f7f7; }
  header {
    background: #1a1a1a; color: #fff; padding: 16px 30px;
    display: flex; justify-content: space-between; align-items: center;
  }
  header h1 { font-family: 'Oswald', sans-serif; font-size: 18px; color: #fb923c; }
  header a { color: #fff; text-decoration: none; font-size: 13px; }
  .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
  .back-link { display:inline-block; margin-bottom: 18px; color:#c2410c; font-weight:700; text-decoration:none; font-size:14px; }
  .titulo-row { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom: 18px; }
  h2 { font-family: 'Oswald', sans-serif; color:#1a1a1a; }
  .mensaje-ok { background:#dcfce7; color:#15803d; padding:10px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; }
  .mensaje-advertencia { background:#fef9c3; color:#854d0e; padding:10px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; }

  .btn-nuevo {
    background:#15803d; color:#fff; border:none; padding:10px 18px;
    border-radius:6px; font-weight:700; font-size:13px; cursor:pointer;
    text-decoration:none; display:inline-flex; align-items:center; gap:6px;
  }
  .btn-nuevo:hover { background:#116530; }

  table { width: 100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom: 36px; }
  th, td { padding: 12px 14px; text-align:left; font-size: 13px; border-bottom: 1px solid #eee; }
  th { background:#fff8f2; color:#c2410c; text-transform:uppercase; font-size:11px; letter-spacing:0.5px; }
  td a { margin-right: 10px; color:#c2410c; text-decoration:none; font-size:12px; font-weight:700; }
  td a.eliminar { color:#b91c1c; }

  .form-box { background:#fff; border-radius:10px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.06); border-top: 4px solid #888; }
  .form-box.modo-editar { border-top-color: #c2410c; }
  .form-box.modo-nuevo { border-top-color: #15803d; }
  .form-box h3 { font-size:15px; margin-bottom:4px; color:#1a1a1a; }
  .form-box .modo-aviso { font-size:12px; color:#888; margin-bottom:16px; }
  label { display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#555; }
  select, input {
    width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px;
    font-size: 13px; margin-bottom: 14px;
  }
  .form-row { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  button {
    background:#c2410c; color:#fff; border:none; padding:10px 22px;
    border-radius:6px; font-weight:700; font-size:13px; cursor:pointer;
  }
  button:hover { background:#a3360a; }
  .cancelar { display:inline-block; margin-left:10px; color:#888; font-size:13px; text-decoration:none; }
</style>
</head>
<body>
  <header>
    <h1><i class="fa-solid fa-bus"></i> Panel Cootrasam</h1>
    <a href="index.php">← Volver al panel</a>
  </header>

  <div class="container">
    <a href="index.php" class="back-link">← Volver</a>

    <div class="titulo-row">
      <h2><i class="fa-solid fa-clock"></i> Horarios</h2>
      <a href="horarios.php?nuevo=1" class="btn-nuevo"><i class="fa-solid fa-plus"></i> Agregar nuevo horario</a>
    </div>

<?php if ($mensaje): ?>
  <?php
    $es_advertencia = (strpos($mensaje, 'Ya existe un horario') !== false);
  ?>
  <div class="<?php echo $es_advertencia ? 'mensaje-advertencia' : 'mensaje-ok'; ?>">
    <?php echo $mensaje; ?>
  </div>
<?php endif; ?>

    <table>
      <tr>
        <th>Origen</th>
        <th>Tipo de día</th>
        <th>Primera salida</th>
        <th>Última salida</th>
        <th>Frecuencia</th>
        <th>Acciones</th>
      </tr>
      <?php while ($fila = $horarios->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($fila['origen']); ?></td>
        <td><?php echo htmlspecialchars($fila['tipo_dia']); ?></td>
        <td><?php echo htmlspecialchars($fila['primera_salida']); ?></td>
        <td><?php echo htmlspecialchars($fila['ultima_salida']); ?></td>
        <td><?php echo htmlspecialchars($fila['frecuencia']); ?></td>
        <td>
          <a href="?editar=<?php echo $fila['id']; ?>">Editar</a>
          <a href="?eliminar=<?php echo $fila['id']; ?>" class="eliminar" onclick="return confirm('¿Seguro que quieres eliminar este horario?');">Eliminar</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>

    <div class="form-box <?php echo $editando ? 'modo-editar' : 'modo-nuevo'; ?>">
      <h3><?php echo $editando ? ' Editando un horario existente' : '+ Agregando un horario NUEVO'; ?></h3>
      <p class="modo-aviso">
        <?php echo $editando
          ? 'Estás modificando la fila de ' . htmlspecialchars($editando['origen']) . ' / ' . htmlspecialchars($editando['tipo_dia']) . '. Si cambias el "Tipo de día" aquí, esa fila se convierte en otra, no se crea una nueva.'
          : 'Este formulario está vacío y va a crear una fila NUEVA en la tabla. '; ?>
      </p>
      <form method="POST">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?php echo $editando ? $editando['id'] : ''; ?>">

        <div class="form-row">
          <div>
            <label>Origen</label>
            <select name="origen" required>
              <option value="Sampués" <?php if ($editando && $editando['origen']=='Sampués') echo 'selected'; ?>>Sampués</option>
              <option value="Sincelejo" <?php if ($editando && $editando['origen']=='Sincelejo') echo 'selected'; ?>>Sincelejo</option>
            </select>
          </div>
          <div>
            <label>Tipo de día</label>
            <select name="tipo_dia" required>
              <option value="semana" <?php if ($editando && $editando['tipo_dia']=='semana') echo 'selected'; ?>>Entre semana</option>
              <option value="sabado" <?php if ($editando && $editando['tipo_dia']=='sabado') echo 'selected'; ?>>Sábado</option>
              <option value="domingo" <?php if ($editando && $editando['tipo_dia']=='domingo') echo 'selected'; ?>>Domingo</option>
            </select>
          </div>
        </div>

        <label>Primera salida</label>
        <input type="text" name="primera_salida" placeholder="Ej: 5:40 a.m." value="<?php echo $editando ? htmlspecialchars($editando['primera_salida']) : ''; ?>" required>

        <label>Última salida</label>
        <input type="text" name="ultima_salida" placeholder="Ej: 8:00 p.m." value="<?php echo $editando ? htmlspecialchars($editando['ultima_salida']) : ''; ?>" required>

        <label>Frecuencia</label>
        <input type="text" name="frecuencia" placeholder="Ej: cada 12 min. aprox." value="<?php echo $editando ? htmlspecialchars($editando['frecuencia']) : ''; ?>" required>

        <button type="submit"><?php echo $editando ? 'Guardar cambios' : 'Agregar horario'; ?></button>
        <?php if ($editando): ?>
          <a href="horarios.php?nuevo=1" class="cancelar">Cancelar</a>
        <?php endif; ?>
      </form>
    </div>
  </div>
</body>
</html>
