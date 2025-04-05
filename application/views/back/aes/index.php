<div class="card">
    <div class="card-header"><?= $title ?></div>
    <div class="card-body">
        <div class="row">
            <div class="col-2">
                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="action_encryp">Encryp</button>
                </div>
            </div>

            <div class="col-10">
                <div class="form-group">
                    <input class="form-control" type="text" name="text">
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <textarea name="hash" class="form-control"></textarea>
                </div>
            </div>

            <div class="col-2">
                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="action_decryp">Decryp</button>
                </div>
            </div>

            <div class="col-10">
                <div class="form-group">
                    <input class="form-control" readonly type="text" name="result">
                </div>
            </div>
            <?= add_csrf_value(); ?>
        </div>

        <div class="row">
            <div class="col-6">
                <label>Password</label>
                <div class="form-group">
                    <input class="form-control" type="text" name="password" value="<?= $password ?>">
                </div>
            </div>
            <div class="col-6">
                <label>Key</label>
                <div class="form-group">
                    <input class="form-control" type="text" name="key" value="<?= $key ?>">
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $("button#action_encryp").click(function(e) {
        e.preventDefault();
        var submit_button = $(this);
        var submit_button_text = submit_button.html();
        $.ajax({
            type: 'POST',
            data: {
                password: $("input[name='password']").val(),
                key: $("input[name='key']").val(),
                encrypt: "yes",
                [csrf_token_name]: csrf_hash,
                text: $("input[name='text']").val()
            },
            dataType: 'json',
            beforeSend: function() {
                submit_button.attr("disabled", "disabled");
                submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
            }
        }).done(function(json) {
            $("textarea[name='hash']").val(json.msg)
        }).always(function() {
            submit_button.html(submit_button_text);
            submit_button.removeAttr("disabled");
        }).fail(function(jqxhr) {
            message_ajax_fail_show(jqxhr);
        });
    });

    $("button#action_decryp").click(function(e) {
        e.preventDefault();
        var submit_button = $(this);
        var submit_button_text = submit_button.html();
        $.ajax({
            type: 'POST',
            data: {
                password: $("input[name='password']").val(),
                key: $("input[name='key']").val(),
                decrypt: "yes",
                [csrf_token_name]: csrf_hash,
                hash: $("textarea[name='hash']").val()
            },
            dataType: 'json',
            beforeSend: function() {
                submit_button.attr("disabled", "disabled");
                submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
            }
        }).done(function(json) {
            $("input[name='result']").val(json.msg)
        }).always(function() {
            submit_button.html(submit_button_text);
            submit_button.removeAttr("disabled");
        }).fail(function(jqxhr) {
            message_ajax_fail_show(jqxhr);
        });
    });
</script>