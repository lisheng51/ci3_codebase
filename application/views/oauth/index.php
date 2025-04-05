<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?= $title ?></title>
    <?php
    echo F_asset::favicon();
    echo F_asset::fontawesome();
    echo F_asset::axios() . F_asset::jquery();
    echo F_asset::bootstrap();
    echo F_asset::sweetalert2();
    echo script_tag(site_url("asset"));
    echo script_tag(sys_asset_url("js/function.js"));
    ?>
</head>

<body>
    <div class="container">
        <div class="card card-login mx-auto mt-3">
            <div class="card-header"><?= $title ?></div>
            <div class="card-body">
                <form method="POST" id="send">
                    <div class="form-group">
                        <input type="email" name="username" autofocus required placeholder="Gebruikersnaam" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" required placeholder="Wachtwoord" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" name="webapp_one_code" maxlength="6" class="form-control form-control-user" value="" placeholder="2FA code" />
                    </div>
                    <div class="form-group">
                        <div class="show_access_code_input"></div>
                    </div>
                    <div class="form-group">
                        <?= add_csrf_value(); ?>
                        <button type="submit" class="btn btn-primary btn-block" id="submit_button">Verzenden</button>
                    </div>
                </form>
            </div>
            <div class="text-center">
                <p>&copy; Copyright <?= date('Y') ?></p>
            </div>
        </div>
    </div>

    <script>
        send_form();

        function send_form() {
            var submit_button = $('#submit_button');
            var submit_button_text = submit_button.html();
            $("form#send").submit(function(e) {
                e.preventDefault();
                if ($('input#webapp_access_code').length) {
                    if ($('#webapp_access_code').val().length === 0) {
                        handle_info_box("error", "Uw toegangscode is leeg");
                        return false;
                    }

                }

                $.ajax({
                    data: $(this).serialize(),
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        submit_button.attr("disabled", "disabled");
                        submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
                    }
                }).done(function(json) {
                    if (json.type_done) {
                        switch (json.type_done) {
                            case 'redirect':
                                if (json.redirect_url) {
                                    window.location.href = json.redirect_url;
                                }
                                break;
                            case 'show_access_code_input':
                                if (json.input_html) {
                                    $('.show_access_code_input').html(json.input_html);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    handle_info_box(json.status, json.msg);
                }).always(function() {
                    submit_button.html(submit_button_text);
                    submit_button.removeAttr("disabled");
                }).fail(function(jqxhr) {
                    message_ajax_fail_show(jqxhr);
                });
            });
        }
    </script>


</body>

</html>