<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Page</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>

    <?php
    session_start();
    include_once __DIR__ . '/../../config/config.php';


    include_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../controllers/loginController.php';


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $loginController = new LoginController($conn);
        $loginController->login($username, $password);
        $error = $loginController->error;
    }

    ?>

    <main class="card">
        <h2>Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Email</label>
                <input type="text" class="form-control" name="username" id="username" required />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="password" id="password" required />
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
            <a href="#" class="text-decoration-none" style="color:#764ba2;">Lupa Password?</a>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/togglePassword.js"></script>

    <!-- footer -->
    <?php
    require_once __DIR__ . '/../components/footer.php';
    ?>
</body>

</html>