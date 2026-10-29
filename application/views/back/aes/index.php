<div class="card">
    <div class="card-header"><?= $title ?></div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-lg-2">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" id="action_encryp">Encryp</button>
                </div>
            </div>

            <div class="col-12 col-lg-10">
                <div class="mb-3">
                    <input class="form-control" type="text" name="text">
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <textarea name="hash" class="form-control"></textarea>
                </div>
            </div>

            <div class="col-12 col-lg-2">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" id="action_decryp">Decryp</button>
                </div>
            </div>

            <div class="col-12 col-lg-10">
                <div class="mb-3">
                    <input class="form-control" readonly type="text" name="result">
                </div>
            </div>
            <?= add_csrf_value(); ?>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <label>Password</label>
                <div class="mb-3">
                    <input class="form-control" type="text" name="password" value="<?= $password ?>">
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <label>Key</label>
                <div class="mb-3">
                    <input class="form-control" type="text" name="key" value="<?= $key ?>">
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $("button#action_encryp").click(function(e) {
        e.preventDefault();
        let submit_button = $(this);
        let submit_button_text = submit_button.html();

        submit_button.attr("disabled", "disabled");
        submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');

        let formData = new FormData();
        formData.set([csrf_token_name], csrf_hash);
        formData.set('password', $("input[name='password']").val());
        formData.set('key', $("input[name='key']").val());
        formData.set('text', $("input[name='text']").val());
        formData.set('encrypt', "yes");

        myfetch({
                method: 'POST',
                body: formData
            }).then(json => {
                $("textarea[name='hash']").val(json.msg)
            })
            .finally(() => {
                submit_button.html(submit_button_text);
                submit_button.removeAttr("disabled");
            });
    });

    $("button#action_decryp").click(function(e) {
        e.preventDefault();
        let submit_button = $(this);
        let submit_button_text = submit_button.html();

        submit_button.attr("disabled", "disabled");
        submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');

        let formData = new FormData();
        formData.set([csrf_token_name], csrf_hash);
        formData.set('password', $("input[name='password']").val());
        formData.set('key', $("input[name='key']").val());
        formData.set('hash', $("textarea[name='hash']").val());
        formData.set('decrypt', "yes");

        myfetch({
                method: 'POST',
                body: formData
            }).then(json => {
                $("input[name='result']").val(json.msg)
            })
            .finally(() => {
                submit_button.html(submit_button_text);
                submit_button.removeAttr("disabled");
            });
    });
</script>