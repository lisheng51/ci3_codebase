<main role="main" class="article-main">
    <section class="article-hero">
        <div class="container">
            <?= $breadcrumb ?>
            <div class="row align-items-end g-4">
                <div class="col-lg-7">
                    <h1 class="article-hero-title"><?= html_escape($title) ?></h1>
                    <p class="article-hero-lead mb-0">
                        <?= $total ?> <?= $total === 1 ? 'artikel' : 'artikelen' ?>
                        <?php if ($keyword !== '') : ?>
                            gevonden voor &ldquo;<?= html_escape($keyword) ?>&rdquo;
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-lg-5">
                    <form class="article-search" method="get" action="<?= site_url($controller_url) ?>">
                        <?php if ($cate_id > 0) : ?>
                            <input type="hidden" name="cate_id" value="<?= $cate_id ?>">
                        <?php endif; ?>
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="q" class="form-control" placeholder="Zoek op titel..." value="<?= html_escape($keyword) ?>">
                        <button type="submit" class="btn btn-primary">Zoeken</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-4 py-lg-5">
        <?php if (!empty($catedb)) : ?>
            <nav class="article-cates mb-4" aria-label="Categorieën">
                <a href="<?= site_url($controller_url) ?>" class="article-cate<?= $cate_id === 0 ? ' active' : '' ?>">Alles</a>
                <?php foreach ($catedb as $cate) : ?>
                    <a href="<?= $cate["url"] ?>" class="article-cate<?= $cate_id === (int)$cate["cate_id"] ? ' active' : '' ?>"><?= html_escape($cate["name"]) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if (empty($listdb)) : ?>
            <div class="article-empty">
                <i class="fa-regular fa-folder-open"></i>
                <h2>Geen artikelen gevonden</h2>
                <p>Probeer een andere zoekterm of categorie.</p>
                <a href="<?= site_url($controller_url) ?>" class="btn btn-outline-primary">Alle artikelen</a>
            </div>
        <?php else : ?>
            <div class="row g-4">
                <?php foreach ($listdb as $rs) : ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="article-card">
                            <div class="article-card-body">
                                <a href="<?= $rs["cate_url"] ?>" class="article-badge"><?= html_escape($rs["cate_name"]) ?></a>
                                <h2 class="article-card-title">
                                    <a href="<?= $rs["url"] ?>" class="stretched-link"><?= html_escape($rs["title"]) ?></a>
                                </h2>
                                <p class="article-card-text"><?= html_escape($rs["summary"]) ?></p>
                            </div>
                            <div class="article-card-meta">
                                <span><i class="fa-regular fa-calendar"></i><?= $rs["date"] ?></span>
                                <span><i class="fa-regular fa-clock"></i><?= $rs["read_minutes"] ?> min</span>
                                <span><i class="fa-regular fa-eye"></i><?= (int)$rs["hits"] ?></span>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($pagination)) : ?>
                <nav class="article-pagination mt-5" aria-label="Paginering">
                    <?= $pagination ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
