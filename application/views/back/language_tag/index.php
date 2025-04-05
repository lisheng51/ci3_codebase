<div class="card">
    <div class="card-header"><?= lang('search_box_header_text') ?><div class="float-right">
            <a href="<?= site_url($path_name . '/language_tag/sync') ?>" class="btn btn-dark btn-sm">Synchroniseren</a>
            <a href="<?= site_url($path_name . '/language_tag/add') ?>" class="btn btn-success btn-sm"><?= lang('add_icon') ?></a>
            <a href="<?= site_url($path_name . '/language_tag/add?mode=tinymce') ?>" class="btn btn-success btn-sm"><i class="fa-fw fa-regular fa-square-plus"></i></a>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" id="form_search">
            <div class="row">
                <div class="col-4">
                    <?= labelSelectInput('Tag', 'tag') ?>
                </div>
                <div class="col-2">
                    <?= labelSelectSelectMulti('Taal', LanguageModel::$primaryKey, LanguageModel::selectMultiple()) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?= add_csrf_value(); ?>
                    <?= search_button() ?>
                    <?= reset_button() ?>
                    <?php foreach (LanguageModel::getAllData() as $rs) : ?>
                        <a href='<?= site_url($path_name . '/Language_tag/view/' . $rs[LanguageModel::$primaryKey]) ?>' class="btn btn-info btn-sm"><?= $rs['name'] ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-2">
                <?= select_order_by(LanguageTagModel::$selectOrderBy); ?>
            </div>
            <div class="col-2">
                <?= select_page_limit() ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12" id="ajax_search_content">
        <?= $result; ?>
    </div>
</div>

<script>
    $("form#form_search").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>