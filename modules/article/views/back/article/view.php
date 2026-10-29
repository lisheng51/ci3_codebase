<div class="card">
    <h5 class="card-header"><?php echo $title ?></h5>
    <div class="card-body">
        <div class="row">
            <div class="col-12 text-break article-content">
                <?php echo $rsdb["content"] ?>
            </div>
        </div>
    </div>
</div>
<style>
    .article-content img,
    .article-content video,
    .article-content iframe {
        max-width: 100%;
        height: auto;
    }

    .article-content table {
        display: block;
        max-width: 100%;
        overflow-x: auto;
    }
</style>
