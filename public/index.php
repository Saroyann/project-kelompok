<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Page</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./css/style.css">
</head>

<body>
  <main class="card">
    <h2>Login</h2>
    <form>
      <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email</label>
        <input type="email" class="form-control" id="email" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="password" required />
          <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
            <span id="togglePasswordIcon">🙈</span>
          </button>
        </div>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" />
        <label class="form-check-label" for="remember">Ingat Saya</label>
      </div>
      <button type="submit" class="btn btn-custom w-100 text-white fw-semibold">Login</button>
    </form>
    <div class="text-center mt-3">
      <a href="#" class="text-decoration-none" style="color:#764ba2;">Forgot password?</a>
    </div>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="./js/togglePassword.js"></script>
  <script>

  </script>

    <?php
    include_once __DIR__ . '/../app/views/footer.php';
    ?>
</body>

</html>