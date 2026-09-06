<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paintarely - Crear cuenta</title>
<link rel="stylesheet" href="/assets/css/estilos.css">
</head>
<body>
<header class="cabecera">
  <h1>🎨 Paintarely</h1>
  <a href="/" class="volver">&larr; Volver a la galería</a>
</header>

<main class="formulario-wrap">
  <form method="POST" action="/registro" class="formulario">
    <h2>Crear cuenta</h2>
    <?php foreach ($errores as $error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
    <label>Nombre
      <input type="text" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
    </label>
    <label>Email
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </label>
    <label>Contraseña (mínimo 8 caracteres)
      <input type="password" name="password" minlength="8" required>
    </label>
    <button type="submit">Crear cuenta</button>
    <p>¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>
  </form>
</main>
</body>
</html>
