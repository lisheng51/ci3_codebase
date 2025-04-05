<ul class="nav nav-tabs">
    <?php
    foreach ($ul as $key => $value) :
        $status = $key === 0 ? "active" : null;
    ?>
        <li class="nav-item"><a class="nav-link <?= $status ?>" href="#<?= $value ?>_lang" data-toggle="tab"><?= $value ?></a></li>
    <?php endforeach; ?>
</ul>

<form method="POST" id="send">
    <div class="tab-content">
        <?php
        foreach ($listdb as $key => $value) :
            $status = $key === 0 ? "active" : null;
            foreach ($value as $did => $data) :
        ?>
                <div id="<?= $did ?>_lang" class="tab-pane <?= $status ?>">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($data as $key => $value) : ?>
                                    <div class="col-3">
                                        <label><?= $key ?></label>
                                        <div class="form-group">
                                            <textarea class="form-control" rows="4" name="<?= $did . '[' . $key . ']' ?>"><?= stripslashes($value) ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
            endforeach;
        endforeach;
        ?>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <div class="form-group">
                <?= add_csrf_value(); ?>
                <?= add_submit_button('config') ?>
            </div>
        </div>
    </div>
</form>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>