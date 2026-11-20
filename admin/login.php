<?php
session_start();
include '../conexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    $sql = "SELECT id, usuario, contrasena, nombre FROM admin_usuarios WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        if (password_verify($contrasena, $fila['contrasena'])) {
            // Login correcto
            $_SESSION['admin_id'] = $fila['id'];
            $_SESSION['admin_nombre'] = $fila['nombre'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
    $stmt->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Admin - Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
  body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1a1a1a 0%, #4a2c14 60%, #a05a24 100%);
  }
  .login-box {
    background: #fff;
    border-radius: 14px;
    padding: 40px 36px;
    width: 100%;
    max-width: 360px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
  }
  .login-box h1 {
    font-family: 'Oswald', sans-serif;
    font-size: 22px;
    color: #c2410c;
    text-align: center;
    margin-bottom: 4px;
  }
  .login-box p.subtitulo {
    text-align: center;
    color: #888;
    font-size: 13px;
    margin-bottom: 24px;
  }
  label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #333; }
  input {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 16px;
  }
  input:focus { outline: none; border-color: #c2410c; }
  button {
    width: 100%;
    padding: 12px;
    background: #c2410c;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
  }
  button:hover { background: #a3360a; }
  .error-msg {
    background: #fee2e2;
    color: #b91c1c;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 16px;
    text-align: center;
  }
</style>
</head>
<body>
  <div class="login-box">
    <h1><i class="fa-solid fa-bus"></i> Cootrasam</h1>
    <p class="subtitulo">Panel administrativo</p>

    <?php if ($error): ?>
      <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="usuario">Usuario</label>
      <input type="text" id="usuario" name="usuario" required autofocus>

      <label for="contrasena">Contraseña</label>
      <input type="password" id="contrasena" name="contrasena" required>

      <button type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>
