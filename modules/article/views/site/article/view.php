<main role="main" class="article-main">
    <section class="article-hero article-hero-detail">
        <div class="container">
            <?= $breadcrumb ?>
            <a href="<?= $rsdb["cate_url"] ?>" class="article-badge"><?= html_escape($rsdb["cate_name"]) ?></a>
            <h1 class="article-hero-title"><?= html_escape($rsdb["title"]) ?></h1>
            <?php if (!empty($rsdb["description"])) : ?>
                <p class="article-hero-lead"><?= html_escape($rsdb["description"]) ?></p>
            <?php endif; ?>
            <div class="article-detail-meta">
                <span><i class="fa-regular fa-calendar"></i><?= $rsdb["date"] ?></span>
                <span><i class="fa-regular fa-clock"></i><?= $rsdb["read_minutes"] ?> min leestijd</span>
                <span><i class="fa-regular fa-eye"></i><?= (int)$rsdb["hits"] ?> keer gelezen</span>
            </div>
        </div>
    </section>

    <div class="container py-4 py-lg-5">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-8">
                <article class="article-content">
                    <?= $rsdb["content"] ?>
                </article>
                <div class="article-back">
                    <a href="<?= site_url($controller_url) ?>" class="btn btn-outline-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i>Terug naar overzicht
                    </a>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="article-sidebar">
                    <?php if (!empty($recentdb)) : ?>
                        <div class="article-widget">
                            <h3 class="article-widget-title">Recente artikelen</h3>
                            <ul class="article-recent">
                                <?php foreach ($recentdb as $rs) : ?>
                                    <li>
                                        <a href="<?= $rs["url"] ?>"><?= html_escape($rs["title"]) ?></a>
                                        <small><?= $rs["date"] ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($catedb)) : ?>
                        <div class="article-widget">
                            <h3 class="article-widget-title">Categorieën</h3>
                            <div class="article-cates">
                                <?php foreach ($catedb as $cate) : ?>
                                    <a href="<?= $cate["url"] ?>" class="article-cate<?= (int)$cate["cate_id"] === (int)$rsdb["cate_id"] ? ' active' : '' ?>"><?= html_escape($cate["name"]) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</main>
