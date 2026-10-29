<div class="card">
    <h5 class="card-header"><?php echo $title . ' - ' . lang('search_box_header_text') ?><div class="float-end"><?php echo $addButton ?></div>
    </h5>
    <div class="card-body">
        <form id="form_search">
            <div class="row">
                <div class="col-3">
                    <?php echo labelSelectInput('Titel', 'search_title') ?>
                </div>
                <div class="col-3">
                    <?php echo labelSelectInput('Omschrijving', 'search_description') ?>
                </div>
                <div class="col-6">
                    <?php echo labelSelectSelectMulti('Categorie', ArticleCateModel::$primaryKey, ArticleCateModel::selectMultiple()) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?php echo add_csrf_value(); ?>
                    <?php echo search_button() ?>
                    <?php echo reset_button() ?>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-2">
                <?php echo select_order_by(ArticleModel::$selectOrderBy); ?>
            </div>
            <div class="col-2">
                <?php echo select_page_limit() ?>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-12" id="ajax_search_content">
        <?php echo $result; ?>
    </div>
</div>

<script>
    $("form#form_search").submit(function(e) {
        e.preventDefault();
        ajax_form_search($(this));
    });
</script>