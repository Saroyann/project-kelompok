<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="./css/style.css">
    <title>Dashboard</title>

    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 220px;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 0;
            z-index: 1000;
        }


        body {
            margin-left: 220px;
        }
    </style>
</head>

<body>
    <!-- navbar -->
    <?php
    require_once __DIR__ . '/../components/navbar.php';
    ?>

    <h1>selamat datang, Admin</h1>

    <!-- footer -->
    <?php
    require_once __DIR__ . '/../components/footer.php';
    ?>
</body>

</html>