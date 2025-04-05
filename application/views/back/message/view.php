<div class="card">
    <div class="card-header"><?= $rsdb["title"] ?></div>
    <div class="card-body">
        <?= $rsdb["content"] ?>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-md-4">
                Van: <?= $rsdb["from_user_name"] ?>
            </div>
            <div class="col-md-4">
                Naar: <?= $rsdb["to_user_name"] ?>
            </div>
            <div class="col-md-4">
                Tijd aangemaakt: <?= $rsdb["date"] ?>
            </div>
        </div>
    </div>
</div>


<div class="card mt-3 <?= $reply_status ?>">
    <div class="card-header">
        <button class="btn btn-info" id="reply_button" data-toggle="collapse" data-target="#reply_content"><i class="fa-fw fas fa-plus"></i> Beantwoord</button> <?= $delButton ?>
    </div>
    <div class="card-body">
        <div id="reply_content" class="panel-collapse collapse">
            <form method="POST" id="send">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Inhoud*</label>
                            <textarea class="form-control tinymce_noxss_clean" name="content"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= add_csrf_value(); ?>
                            <input type="hidden" name="title" value="<?= $rsdb["title"]; ?>" />
                            <input type="hidden" name="to_user_id" value="<?= $rsdb["from_user_id"]; ?>" />
                            <?= add_submit_button() ?>
                            <?= add_reset_button() ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    setup_tinymce_noxss_clean();
    $("form#send").submit(function(e) {
        tinymce.triggerSave();
        e.preventDefault();
        ajax_form_search($(this));
    });
    $("button#reply_button").click(function() {
        var $this = $(this);
        var icon = $this.find('i');
        if (icon.hasClass('fa-plus')) {
            $this.find('i').removeClass('fa-plus').addClass('fa-minus');
        } else {
            $this.find('i').removeClass('fa-minus').addClass('fa-plus');
        }
    });
</script>