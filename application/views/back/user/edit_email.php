<div class="card">
    <div class="card-header"><?= $title ?></div>
    <div class="card-body">
        <form id="send" method="POST">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Huidig emailadres</label>
                        <input type="email" disabled class="form-control" value="<?= $rsdb["emailaddress"] ?>" placeholder="emailaddress">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nieuw emailadres*</label>
                        <input type="email" name="emailaddress" required class="form-control" placeholder="emailaddress">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Wachtwoord*</label>
                        <input type="password" name="password" required class="form-control" placeholder="*********">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group input-group show_form_input"></div>
                    <div class="form-group">
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button($rsdb) ?>
                        <?= add_reset_button() ?>
                        <a href="<?= site_url(AccessCheckModel::$backPath . '/User/profile') ?>" class="btn btn-dark">Terug naar profiel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $("form#send").submit(function(e) {
        $.ajax({
            data: $(this).serialize(),
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                $('#submit_button').prop("disabled", true);
            }
        }).done(function(json) {
            if (json.type_done) {
                switch (json.type_done) {
                    case 'redirect':
                        if (json.redirect_url) {
                            window.location.href = json.redirect_url;
                        }
                        break;
                    case 'show_form_input':
                        if (json.input_html) {
                            $('.show_form_input').html(json.input_html);
                        }
                        break;
                    case 'change_label':
                        if (json.input_html) {
                            $('.show_form_input').html(json.input_html);
                            location.reload();
                        }
                        break;
                    default:
                        break;
                }
            }
            handle_info_box(json.status, json.msg);
            $('#submit_button').prop("disabled", false);
        }).fail(function(jqxhr) {
            message_ajax_fail_show(jqxhr);
        });
        e.preventDefault();
    });
</script>