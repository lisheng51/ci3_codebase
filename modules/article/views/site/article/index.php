<div role="main" class="main">
    <section class="page-header">
        <div class="container">
            <?php echo $breadcrumb ?>
            <div class="row">
                <div class="col-md-12">
                    <h1><?php echo $title ?></h1>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="list-group">
                    <?php foreach ($listdb as $rs) : ?>
                        <li class="list-group-item"><a href="<?php echo $rs["url"]; ?>"><?php echo $rs["title"]; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

</div>