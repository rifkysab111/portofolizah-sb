<?php

require __DIR__ . '/../config/config.php';

//require_admin();

$success = '';
$error = '';

/*
|--------------------------------------------------------------------------
| PROCESS FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    try {

        /*
        |--------------------------------------------------------------------------
        | UPDATE PROFILE + HERO PHOTO
        |--------------------------------------------------------------------------
        */

        if ($action === 'profile') {

            $oldProfile = $pdo
                ->query(
                    'SELECT *
                     FROM profiles
                     WHERE id = 1'
                )
                ->fetch();

            /*
            | Upload hero photo jika user memilih file.
            | Jika tidak memilih file, hasilnya NULL.
            */
            $newHeroImage = upload_image(
                $_FILES['hero_image'] ?? [],
                'hero_'
            );

            $name = trim(
                $_POST['name'] ?? ''
            );

            $subtitle = trim(
                $_POST['subtitle'] ?? ''
            );

            $bio = trim(
                $_POST['bio'] ?? ''
            );

            $email = trim(
                $_POST['email'] ?? ''
            );

            $instagram = trim(
                $_POST['instagram'] ?? ''
            );

            $whatsapp = trim(
                $_POST['whatsapp'] ?? ''
            );

            /*
            |--------------------------------------------------------------------------
            | Jika ada foto baru
            |--------------------------------------------------------------------------
            */

            if ($newHeroImage) {

                $stmt = $pdo->prepare(
                    'UPDATE profiles
                     SET
                        name = ?,
                        subtitle = ?,
                        bio = ?,
                        email = ?,
                        instagram = ?,
                        whatsapp = ?,
                        hero_image = ?
                     WHERE id = 1'
                );

                $stmt->execute([
                    $name,
                    $subtitle,
                    $bio,
                    $email,
                    $instagram,
                    $whatsapp,
                    $newHeroImage
                ]);

                /*
                | Hapus foto lama setelah foto baru berhasil disimpan.
                */
                delete_old_file(
                    $oldProfile['hero_image'] ?? null
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | Jika tidak upload foto,
                | hanya update informasi profile.
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare(
                    'UPDATE profiles
                     SET
                        name = ?,
                        subtitle = ?,
                        bio = ?,
                        email = ?,
                        instagram = ?,
                        whatsapp = ?
                     WHERE id = 1'
                );

                $stmt->execute([
                    $name,
                    $subtitle,
                    $bio,
                    $email,
                    $instagram,
                    $whatsapp
                ]);
            }

            $success = 'Profil berhasil diperbarui.';
        }


        /*
        |--------------------------------------------------------------------------
        | REMOVE HERO PHOTO
        |--------------------------------------------------------------------------
        */

        if ($action === 'remove_hero') {

            $oldProfile = $pdo
                ->query(
                    'SELECT hero_image
                     FROM profiles
                     WHERE id = 1'
                )
                ->fetch();

            $pdo->exec(
                'UPDATE profiles
                 SET hero_image = NULL
                 WHERE id = 1'
            );

            delete_old_file(
                $oldProfile['hero_image'] ?? null
            );

            $success = 'Foto hero berhasil dihapus.';
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE PORTFOLIO
        |--------------------------------------------------------------------------
        */

        if ($action === 'portfolio') {

            $id = (int) (
                $_POST['id'] ?? 0
            );

            if ($id <= 0) {
                throw new RuntimeException(
                    'ID portfolio tidak valid.'
                );
            }

            /*
            | Ambil foto lama.
            */
            $stmt = $pdo->prepare(
                'SELECT image
                 FROM portfolios
                 WHERE id = ?'
            );

            $stmt->execute([
                $id
            ]);

            $old = $stmt->fetch();

            if (!$old) {
                throw new RuntimeException(
                    'Data portfolio tidak ditemukan.'
                );
            }

            /*
            | Upload foto baru jika dipilih.
            */
            $newImage = upload_image(
                $_FILES['image'] ?? [],
                'portfolio_'
            );

            $title = trim(
                $_POST['title'] ?? ''
            );

            $description = trim(
                $_POST['description'] ?? ''
            );

            /*
            |--------------------------------------------------------------------------
            | Jika ada foto baru
            |--------------------------------------------------------------------------
            */

            if ($newImage) {

                $stmt = $pdo->prepare(
                    'UPDATE portfolios
                     SET
                        title = ?,
                        description = ?,
                        image = ?
                     WHERE id = ?'
                );

                $stmt->execute([
                    $title,
                    $description,
                    $newImage,
                    $id
                ]);

                /*
                | Hapus foto lama.
                */
                delete_old_file(
                    $old['image'] ?? null
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | Jika tidak ada foto baru,
                | hanya update teks.
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare(
                    'UPDATE portfolios
                     SET
                        title = ?,
                        description = ?
                     WHERE id = ?'
                );

                $stmt->execute([
                    $title,
                    $description,
                    $id
                ]);
            }

            $success =
                'Kategori portfolio berhasil diperbarui.';
        }


        /*
        |--------------------------------------------------------------------------
        | REMOVE PORTFOLIO PHOTO
        |--------------------------------------------------------------------------
        */

        if ($action === 'remove_image') {

            $id = (int) (
                $_POST['id'] ?? 0
            );

            if ($id <= 0) {
                throw new RuntimeException(
                    'ID portfolio tidak valid.'
                );
            }

            $stmt = $pdo->prepare(
                'SELECT image
                 FROM portfolios
                 WHERE id = ?'
            );

            $stmt->execute([
                $id
            ]);

            $old = $stmt->fetch();

            if (!$old) {
                throw new RuntimeException(
                    'Data portfolio tidak ditemukan.'
                );
            }

            $stmt = $pdo->prepare(
                'UPDATE portfolios
                 SET image = NULL
                 WHERE id = ?'
            );

            $stmt->execute([
                $id
            ]);

            delete_old_file(
                $old['image'] ?? null
            );

            $success =
                'Foto portfolio berhasil dihapus.';
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE ADMIN ACCOUNT
        |--------------------------------------------------------------------------
        */

        if ($action === 'account') {

            $username = trim(
                $_POST['username'] ?? ''
            );

            $password = $_POST['password'] ?? '';

            $confirmPassword =
                $_POST['confirm_password'] ?? '';


            /*
            | Username tidak boleh kosong.
            */
            if ($username === '') {

                throw new RuntimeException(
                    'Username tidak boleh kosong.'
                );
            }


            /*
            | Cek apakah username sudah digunakan
            | akun lain.
            */
            $stmt = $pdo->prepare(
                'SELECT id
                 FROM admins
                 WHERE username = ?
                   AND id != ?
                 LIMIT 1'
            );

            $stmt->execute([
                $username,
                $_SESSION['admin_id']
            ]);

            if ($stmt->fetch()) {

                throw new RuntimeException(
                    'Username tersebut sudah digunakan.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Jika password diisi,
            | maka password ikut diganti.
            |--------------------------------------------------------------------------
            */

            if ($password !== '') {

                /*
                | Minimal 8 karakter.
                */
                if (
                    strlen($password) < 8
                ) {

                    throw new RuntimeException(
                        'Password minimal 8 karakter.'
                    );
                }


                /*
                | Konfirmasi password.
                */
                if (
                    $password !== $confirmPassword
                ) {

                    throw new RuntimeException(
                        'Konfirmasi password tidak cocok.'
                    );
                }


                /*
                | Hash password.
                */
                $hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                /*
                | Update username + password.
                */
                $stmt = $pdo->prepare(
                    'UPDATE admins
                     SET
                        username = ?,
                        password = ?
                     WHERE id = ?'
                );

                $stmt->execute([
                    $username,
                    $hash,
                    $_SESSION['admin_id']
                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | Jika password kosong,
                | hanya username yang diubah.
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare(
                    'UPDATE admins
                     SET username = ?
                     WHERE id = ?'
                );

                $stmt->execute([
                    $username,
                    $_SESSION['admin_id']
                ]);
            }


            /*
            | Update username pada session.
            */
            $_SESSION['admin_username'] =
                $username;


            $success =
                'Akun admin berhasil diperbarui.';
        }

    } catch (Throwable $e) {

        $error = $e->getMessage();
    }
}


/*
|--------------------------------------------------------------------------
| GET PROFILE
|--------------------------------------------------------------------------
*/

$profile = $pdo
    ->query(
        'SELECT *
         FROM profiles
         WHERE id = 1'
    )
    ->fetch();


/*
|--------------------------------------------------------------------------
| GET PORTFOLIO
|--------------------------------------------------------------------------
*/

$items = $pdo
    ->query(
        'SELECT *
         FROM portfolios
         ORDER BY sort_order ASC, id ASC'
    )
    ->fetchAll();


/*
|--------------------------------------------------------------------------
| SAFE HERO IMAGE
|--------------------------------------------------------------------------
*/

$heroImage =
    $profile['hero_image'] ?? null;


/*
|--------------------------------------------------------------------------
| CURRENT ADMIN USERNAME
|--------------------------------------------------------------------------
*/

$currentUsername =
    $_SESSION['admin_username'] ?? '';

?>

<!doctype html>

<html lang="id">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Dashboard — PORTOFOLIZAH
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


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<header class="admin-header">

    <div>

        <b>
            PORTOFOLIZAH
        </b>

        <span>
            / Dashboard
        </span>

    </div>


    <div class="admin-actions">

        <a
            href="../"
            target="_blank"
        >
            Lihat website ↗
        </a>


        <a href="logout.php">
            Logout
        </a>

    </div>

</header>



<!-- =========================================================
     DASHBOARD
========================================================= -->

<main class="dashboard">


    <!-- =====================================================
         INTRO
    ====================================================== -->

    <div class="intro">

        <div>

            <p class="eyebrow">
                ADMIN PANEL
            </p>


            <h1>
                Ini buat ngeditnya yaa cess..
            </h1>


            <p class="muted">

                Bisa ganti foto, ganti profil yaww

            </p>

        </div>


        <div class="mini-status">

            ● Online

            <br>

            <small>
                PHP + MySQL
            </small>

        </div>

    </div>



    <!-- =====================================================
         SUCCESS MESSAGE
    ====================================================== -->

    <?php if ($success): ?>

        <div class="notice success">

            ✓
            <?= e($success) ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         ERROR MESSAGE
    ====================================================== -->

    <?php if ($error): ?>

        <div class="notice error">

            ⚠
            <?= e($error) ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         PROFILE
    ====================================================== -->

    <section class="panel">


        <div class="panel-head">

            <div>

                <p class="eyebrow">
                    01 / PROFILE
                </p>


                <h2>
                    Informasi utama
                </h2>


                <p class="muted">

                    Buat headingnya ini cess..
                    Ntar buat portofolio ada dibawah, scroll ajaa

                </p>

            </div>

        </div>



        <!-- PROFILE FORM -->

        <form
            method="post"
            enctype="multipart/form-data"
            class="form-grid"
        >

            <input
                type="hidden"
                name="action"
                value="profile"
            >


            <!-- HERO PHOTO -->

            <div class="hero-admin full">


                <!-- PREVIEW -->

                <div
                    class="hero-admin-preview
                    <?= !empty($heroImage)
                        ? 'has-image'
                        : '' ?>"

                    <?php if (!empty($heroImage)): ?>

                        style="
                            background-image:
                            url('../<?= e($heroImage) ?>')
                        "

                    <?php endif; ?>
                >

                    <?php if (empty($heroImage)): ?>

                        <span>
                            AININNA
                        </span>

                    <?php endif; ?>

                </div>



                <!-- HERO COPY -->

                <div class="hero-admin-copy">

                    <strong>
                        Hero Photo
                    </strong>


                    <p class="muted">

                        Fotonya yang bagus yaa, diliat mama dita soalnya
                        Paling bagus pake rasio 4:5

                    </p>


                    <label>

                        Upload / ganti foto

                        <input
                            type="file"
                            name="hero_image"
                            accept="image/jpeg,image/png,image/webp"
                        >

                    </label>


                    <div class="form-row">


                        <button
                            type="submit"
                        >
                            Simpan foto + profil ↗
                        </button>


                        <?php if (!empty($heroImage)): ?>

                            <button
                                class="danger"
                                type="submit"
                                name="action"
                                value="remove_hero"
                                onclick="
                                    return confirm(
                                        'Hapus foto hero?'
                                    );
                                "
                            >
                                Hapus foto
                            </button>

                        <?php endif; ?>


                    </div>

                </div>

            </div>



            <!-- NAME -->

            <label>

                Nama

                <input
                    type="text"
                    name="name"
                    value="<?= e(
                        $profile['name'] ?? ''
                    ) ?>"
                    required
                >

            </label>



            <!-- SUBTITLE -->

            <label>

                Subtitle

                <input
                    type="text"
                    name="subtitle"
                    value="<?= e(
                        $profile['subtitle'] ?? ''
                    ) ?>"
                    required
                >

            </label>



            <!-- BIO -->

            <label class="full">

                Bio

                <textarea
                    name="bio"
                    rows="5"
                    required
                ><?= e(
                    $profile['bio'] ?? ''
                ) ?></textarea>

            </label>



            <!-- EMAIL -->

            <label>

                Email

                <input
                    type="email"
                    name="email"
                    value="<?= e(
                        $profile['email'] ?? ''
                    ) ?>"
                >

            </label>



            <!-- INSTAGRAM -->

            <label>

                Instagram

                <input
                    type="text"
                    name="instagram"
                    value="<?= e(
                        $profile['instagram'] ?? ''
                    ) ?>"
                    placeholder="@username"
                >

            </label>



            <!-- WHATSAPP -->

            <label>

                WhatsApp

                <input
                    type="text"
                    name="whatsapp"
                    value="<?= e(
                        $profile['whatsapp'] ?? ''
                    ) ?>"
                    placeholder="628xxxxxxxxxx"
                >

            </label>


        </form>

    </section>



    <!-- =====================================================
         GALLERY
    ====================================================== -->

    <section class="panel">


        <div class="panel-head">

            <div>

                <p class="eyebrow">
                    02 / GALLERY
                </p>


                <h2>
                    Kelola empat kategori
                </h2>

            </div>


            <span class="muted">

                JPG / PNG / WEBP · max 5 MB

            </span>

        </div>



        <div class="portfolio-admin-grid">


            <?php foreach ($items as $item): ?>


                <form
                    method="post"
                    enctype="multipart/form-data"
                    class="portfolio-form"
                >


                    <input
                        type="hidden"
                        name="action"
                        value="portfolio"
                    >


                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $item['id'] ?>"
                    >



                    <!-- IMAGE -->

                    <div
                        class="thumb"

                        <?php if (
                            !empty($item['image'])
                        ): ?>

                            style="
                                background-image:
                                url(
                                    '../<?= e(
                                        $item['image']
                                    ) ?>'
                                )
                            "

                        <?php endif; ?>
                    >

                        <?php if (
                            empty($item['image'])
                        ): ?>

                            <span>

                                <?= e(
                                    substr(
                                        $item['category'],
                                        0,
                                        1
                                    )
                                ) ?>

                            </span>

                        <?php endif; ?>

                    </div>



                    <!-- CATEGORY -->

                    <div class="category-name">

                        <?= e(
                            $item['category']
                        ) ?>

                    </div>



                    <!-- TITLE -->

                    <label>

                        Judul

                        <input
                            type="text"
                            name="title"
                            value="<?= e(
                                $item['title']
                            ) ?>"
                        >

                    </label>



                    <!-- DESCRIPTION -->

                    <label>

                        Deskripsi

                        <textarea
                            name="description"
                            rows="3"
                        ><?= e(
                            $item['description']
                        ) ?></textarea>

                    </label>



                    <!-- UPLOAD -->

                    <label>

                        Ganti foto

                        <input
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                        >

                    </label>



                    <!-- BUTTON -->

                    <div class="form-row">


                        <button
                            type="submit"
                        >
                            Simpan perubahan
                        </button>



                        <?php if (
                            !empty($item['image'])
                        ): ?>

                            <button
                                class="danger"
                                type="submit"
                                name="action"
                                value="remove_image"
                                onclick="
                                    return confirm(
                                        'Hapus foto kategori ini?'
                                    );
                                "
                            >
                                Hapus foto
                            </button>

                        <?php endif; ?>


                    </div>


                </form>


            <?php endforeach; ?>


        </div>

    </section>



    <!-- =====================================================
         ACCOUNT
    ====================================================== -->

    <section class="panel">


        <div class="panel-head">

            <div>

                <p class="eyebrow">
                    03 / ACCOUNT
                </p>


                <h2>
                    Pengaturan akun admin
                </h2>


                <p class="muted">

                    Gunakan bagian ini untuk
                    mengganti username dan password
                    administrator.

                </p>

            </div>

        </div>



        <!-- ACCOUNT FORM -->

        <form
            method="post"
            class="form-grid"
        >


            <input
                type="hidden"
                name="action"
                value="account"
            >



            <!-- USERNAME -->

            <label>

                Username Baru

                <input
                    type="text"
                    name="username"
                    value="<?= e(
                        $currentUsername
                    ) ?>"
                    autocomplete="username"
                    required
                >

            </label>



            <div></div>



            <!-- NEW PASSWORD -->

            <label>

                Password Baru

                <input
                    type="password"
                    name="password"
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Kosongkan jika tidak diubah"
                >

                <small
                    class="muted"
                >
                    Minimal 8 karakter.
                </small>

            </label>



            <!-- CONFIRM PASSWORD -->

            <label>

                Konfirmasi Password Baru

                <input
                    type="password"
                    name="confirm_password"
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Ulangi password baru"
                >

                <small
                    class="muted"
                >
                    Harus sama dengan password baru.
                </small>

            </label>



            <!-- SUBMIT -->

            <div class="full">

                <button
                    type="submit"
                >
                    Simpan Perubahan Akun ↗
                </button>

            </div>


        </form>

    </section>



    <!-- =====================================================
         TIPS
    ====================================================== -->

    <section class="panel tips">


        <p class="eyebrow">
            04 / TIPS
        </p>


        <h2>
            Supaya visual tetap aesthetic
        </h2>


        <p class="muted">

            Gunakan foto portrait 4:5
            atau sekitar 1080 × 1350 px.

            Pilih foto dengan pencahayaan
            lembut, latar bersih, dan warna
            yang sejalan dengan nuansa
            pink/cream website.

        </p>


    </section>


</main>

</body>

</html>