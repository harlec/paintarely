<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paintarely - Iniciar sesión</title>
<link rel="stylesheet" href="/assets/css/estilos.css">
</head>
<body>
<header class="cabecera">
  <h1>🎨 Paintarely</h1>
  <a href="/" class="volver">&larr; Volver a la galería</a>
</header>

<main class="formulario-wrap">
  <form method="POST" action="/login" class="formulario">
    <h2>Iniciar sesión</h2>
    <?php foreach ($errores as $error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
    <label>Email
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </label>
    <label>Contraseña
      <input type="password" name="password" required>
    </label>
    <button type="submit">Entrar</button>
    <p>¿No tienes cuenta? <a href="/registro">Regístrate</a></p>
  </form>
</main>
</body>
</html>
