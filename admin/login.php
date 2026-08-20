<?php

require __DIR__ . '/../config/config.php';

if (is_admin()) {

    header('Location: index.php');
    exit;
}

$error = '';

if (
    $_SERVER['REQUEST_METHOD']
    === 'POST'
) {

    $username =
        trim(
            $_POST['username'] ?? ''
        );

    $password =
        $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT *
         FROM admins
         WHERE username = ?
         LIMIT 1'
    );

    $stmt->execute([
        $username
    ]);

    $admin = $stmt->fetch();

    if (
        $admin &&
        password_verify(
            $password,
            $admin['password']
        )
    ) {

        session_regenerate_id(true);

        $_SESSION['admin_id'] =
            $admin['id'];

        $_SESSION['admin_username'] =
            $admin['username'];

        header(
            'Location: index.php'
        );

        exit;
    }

    $error =
        'Username atau password salah.';
}

?>

<!doctype html>

<html lang="id">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width,initial-scale=1"
    >

    <title>
        Admin Login — PORTOFOLIZAH
    </title>

    <link
        rel="stylesheet"
        href="/admin/admin.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600&display=swap"
        rel="stylesheet"
    >

</head>

<body class="auth-page">

    <div class="auth-card">

        <p class="eyebrow">
            PORTOFOLIZAH / ADMIN
        </p>

        <h1>
            Welcome back.
        </h1>

        <p class="muted">
            Kelola foto dan informasi portfolio
            dari satu tempat.
        </p>


        <?php if ($error): ?>

            <div class="alert">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <form
            method="post"
        >

            <label>

                Username

                <input
                    type="text"
                    name="username"
                    required
                    autocomplete="username"
                >

            </label>


            <label>

                Password

                <input
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >

            </label>


            <button type="submit">
                Masuk ke Dashboard ↗
            </button>

        </form>


        <p class="hint">

            Default:
            <b>admin</b>
            /
            <b>admin12345</b>.

            Segera ganti password
            untuk production.

        </p>


        <a
            class="back"
            href="../"
        >
            ← Kembali ke website
        </a>

    </div>

</body>

</html>