<ul class="nav nav-tabs">
    <?php
    foreach ($listdb as $key => $value) :
        $status = $key === 0 ? "active" : null;
    ?>
        <li class="nav-item"><a class="nav-link <?= $status ?>" href="#mail_temp_<?= $value[MailTempModel::$primaryKey] ?>" data-toggle="tab"><?= $value['trigger_name'] ?></a></li>
    <?php endforeach ?>
</ul>

<form id="send">
    <div class="tab-content">
        <?php
        foreach ($listdb as $key => $value) :
            $status = $key === 0 ? "active" : null;
        ?>
            <div id="mail_temp_<?= $value[MailTempModel::$primaryKey] ?>" class="tab-pane <?= $status ?>">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Onderwerp*</label>
                                    <input type="text" maxlength="100" class="form-control" name="mail_temp_subject[<?= $value[MailTempModel::$primaryKey] ?>]" required value="<?= $value["subject"] ?? ""; ?>" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label>Inhoud*</label>
                                <div class="form-group">
                                    <textarea class="form-control tinymce" name="mail_temp_body[<?= $value[MailTempModel::$primaryKey] ?>]"><?= $value["body"] ?? ""; ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <div class="form-group">
                <?= add_csrf_value(); ?>
                <?= add_submit_button('mail_temp') ?>
            </div>
        </div>
    </div>
</form>

<script>
    $("form#send").submit(function(e) {
        tinyMCE.triggerSave();
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>