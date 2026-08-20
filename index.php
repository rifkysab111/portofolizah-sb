<?php

require __DIR__ . '/config/config.php';

$profile = $pdo
    ->query(
        'SELECT * FROM profiles WHERE id = 1'
    )
    ->fetch();

$items = $pdo
    ->query(
        'SELECT * FROM portfolios
         ORDER BY sort_order ASC, id ASC'
    )
    ->fetchAll();

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
        PORTOFOLIZAH — <?= e($profile['name']) ?>
    </title>

    <meta
        name="description"
        content="<?= e($profile['subtitle']) ?>"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

    <div class="cloud cloud-a"></div>
    <div class="cloud cloud-b"></div>

    <div class="sparkle s1">✦</div>
    <div class="sparkle s2">✧</div>
    <div class="sparkle s3">·</div>


    <header
        class="site-header"
        id="top"
    >

        <a
            class="brand"
            href="#top"
        >
            PORTOFOLIZAH<span>®</span>
        </a>


        <nav>

            <a href="#about">
                About Me
            </a>

            <a href="#gallery">
                Gallery
            </a>

            <a href="#contact">
                Contact
            </a>

        </nav>


        <div class="header-actions">

            <a
                class="admin-link"
                href="admin/login.php"
            >
                Admin
                <span>↗</span>
            </a>

            <a
                class="header-pill"
                href="#contact"
            >
                Let's connect
                <span>↗</span>
            </a>

        </div>

    </header>


    <main>


        <!-- HERO -->

        <section class="hero section-pad">

            <div class="hero-copy reveal">

                <p class="eyebrow">
                    PORTFOLIO OF
                </p>

                <h1>
                    <?= e($profile['name']) ?>
                </h1>

                <p class="hero-subtitle">
                    <?= e($profile['subtitle']) ?>
                </p>


                <div class="hero-actions">

                    <a
                        class="btn btn-primary"
                        href="#gallery"
                    >
                        Explore my work
                        <span>↓</span>
                    </a>

                    <a
                        class="text-link"
                        href="#about"
                    >
                        Get to know me
                        <span>↗</span>
                    </a>

                </div>

            </div>


            <div class="hero-card reveal delay-1">

                <div
                    class="portrait-placeholder
                    <?= !empty($profile['hero_image'])
                        ? 'has-hero-image'
                        : '' ?>"
                >

                    <?php if (!empty($profile['hero_image'])): ?>

                        <img
                            class="hero-portrait"
                            src="<?= e($profile['hero_image']) ?>"
                            alt="Foto profil <?= e($profile['name']) ?>"
                        >

                    <?php else: ?>

                        <div class="portrait-orb"></div>

                    <?php endif; ?>


                    <span class="bubble">
                        voice • presence • story
                    </span>


                    <div class="portrait-glow"></div>


                    <div class="portrait-label">

                        AININNA

                        <br>

                        <small>
                            HALIZAH RAHMA
                        </small>

                    </div>

                </div>

            </div>

        </section>



        <!-- ABOUT -->

        <section
            class="about section-pad"
            id="about"
        >

            <div class="section-heading reveal">

                <p class="eyebrow">
                    01 / ABOUT ME
                </p>

                <h2>
                    A voice that feels
                    <em>close.</em>
                </h2>

            </div>


            <div class="about-grid">

                <div class="quote-card reveal">

                    “Every stage has a story.
                    I help make yours worth remembering.”

                </div>


                <div class="about-text reveal delay-1">

                    <p>
                        <?= nl2br(
                            e($profile['bio'])
                        ) ?>
                    </p>

                    <a
                        class="text-link"
                        href="#contact"
                    >
                        Work with me
                        <span>↗</span>
                    </a>

                </div>

            </div>

        </section>



        <!-- GALLERY -->

        <section
            class="gallery section-pad"
            id="gallery"
        >

            <div class="section-heading reveal">

                <p class="eyebrow">
                    02 / GALLERY
                </p>

                <h2>
                    Four sides of
                    <em>my craft.</em>
                </h2>

                <p class="section-note">

                    __________________________

                </p>

            </div>


            <div class="gallery-grid">

                <?php foreach (
                    $items as $index => $item
                ): ?>

                    <article
                        class="gallery-card reveal
                        <?= $index % 2
                            ? 'delay-1'
                            : '' ?>"
                    >

                        <div
                            class="gallery-image
                            <?= $item['image']
                                ? 'has-image'
                                : '' ?>"

                            <?php if (
                                $item['image']
                            ): ?>

                                style="
                                    background-image:url(
                                        '<?= e($item['image']) ?>'
                                    )
                                "

                            <?php endif; ?>
                        >

                            <?php if (
                                !$item['image']
                            ): ?>

                                <div class="placeholder-art">

                                    <span>
                                        <?= e(
                                            substr(
                                                $item['category'],
                                                0,
                                                1
                                            )
                                        ) ?>
                                    </span>

                                </div>

                            <?php endif; ?>


                            <div class="card-number">

                                0<?= $index + 1 ?>

                            </div>

                        </div>


                        <div class="gallery-info">

                            <span>

                                <?= e(
                                    $item['category']
                                ) ?>

                            </span>


                            <h3>

                                <?= e(
                                    $item['title']
                                ) ?>

                            </h3>


                            <p>

                                <?= e(
                                    $item['description']
                                ) ?>

                            </p>


                            <span
                                class="circle-arrow"
                            >
                                ↗
                            </span>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        </section>



        <!-- CONTACT -->

        <section
            class="contact section-pad"
            id="contact"
        >

            <div class="contact-card reveal">

                <div>

                    <p class="eyebrow">
                        03 / CONTACT
                    </p>

                    <h2>
                        Let's create something
                        <em>memorable.</em>
                    </h2>

                </div>


                <div class="contact-list">

                    <?php if (
                        $profile['email']
                    ): ?>

                        <a
                            href="mailto:<?= e(
                                $profile['email']
                            ) ?>"
                        >

                            Email

                            <span>
                                <?= e(
                                    $profile['email']
                                ) ?>
                                ↗
                            </span>

                        </a>

                    <?php endif; ?>


                    <?php if (
                        $profile['instagram']
                    ): ?>

                        <a
                            href="https://instagram.com/<?= e(
                                ltrim(
                                    $profile['instagram'],
                                    '@'
                                )
                            ) ?>"
                            target="_blank"
                            rel="noreferrer"
                        >

                            Instagram

                            <span>
                                <?= e(
                                    $profile['instagram']
                                ) ?>
                                ↗
                            </span>

                        </a>

                    <?php endif; ?>


                    <?php if (
                        $profile['whatsapp']
                    ): ?>

                        <a
                            href="https://wa.me/<?= e(
                                preg_replace(
                                    '/[^0-9]/',
                                    '',
                                    $profile['whatsapp']
                                )
                            ) ?>"
                            target="_blank"
                            rel="noreferrer"
                        >

                            WhatsApp

                            <span>
                                Let's talk ↗
                            </span>

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    </main>


    <footer>

        <span>
            PORTOFOLIZAH
        </span>

        <span>
            Made with soft energy & good stories.
        </span>

        <a
            class="footer-admin"
            href="admin/login.php"
        >
            Admin Login ↗
        </a>

    </footer>


    <script
        src="assets/js/app.js"
    ></script>

</body>

</html>