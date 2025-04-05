<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Verzonden</th>
                        <th>Onderwerp</th>
                        <th>Van</th>
                        <th>Naar</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr id="<?= $value["mail_id"]; ?>">
                            <td><button type="button" class="btn btn-danger btn-sm delButton" data-search_data="<?= $value["mail_id"]; ?>" data-del_link="<?= $value["del_url"] ?>"><?= lang("del_icon") ?></button> <button class="btn btn-success btn-sm" data-toggle="collapse" data-target="#order_id_<?= $value["mail_id"]; ?>"><?= $value["send_date_view"] ?></button> </td>
                            <td><a href="<?= $value["view_url"] ?>" target="_blank"><?= $value["subject"] ?></a></td>
                            <td><?= $value["from_email"] ?></td>
                            <td><?= $value["to_email"] ?></td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm view_mail" data-search_data="<?= $value["mail_id"]; ?>"><?= lang("view_icon") ?></button>
                                <button type="button" class="btn btn-success btn-sm send_mail" data-search_data="<?= $value["mail_id"]; ?>"><i class="fas fa-paper-plane fa-fw"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5">
                                <ul class="list-group collapse" id="order_id_<?= $value["mail_id"]; ?>">
                                    <li class="list-group-item">Gelezen: <?= $value["open_date_view"]; ?></li>
                                    <li class="list-group-item">Cc: <?= $value["cc"]; ?></li>
                                    <li class="list-group-item">Bcc: <?= $value["bcc"]; ?></li>
                                    <li class="list-group-item">Reply: <?= $value["reply"]; ?></li>
                                    <li class="list-group-item"><?= $value["file"]; ?> <?= $value["attach"]; ?></li>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <?= $pagination; ?>
    </div>
</div>


<script>
    $("button.send_mail").click(function() {
        var id = $(this).data('search_data');
        var button = $(this);
        var or_text = button.html();
        var ajaxurl = site_url + "ajax/mail/send";
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                [csrf_token_name]: csrf_hash,
                mail_id: id,
                type: "sys"
            },
            beforeSend: function() {
                button.html('<i class="fa fa-fw fa-spinner fa-pulse"></i> ');
            }
        }).done(function(json) {
            handle_info_box(json.status, json.msg);
        }).fail(function(jqxhr) {
            message_ajax_fail_show(jqxhr);
        }).always(function() {
            button.html(or_text);
        });
    });

    $("button.view_mail").click(function() {
        var id = $(this).data('search_data');
        var button = $(this);
        var or_text = button.html();
        var ajaxurl = site_url + "ajax/mail/view";
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                [csrf_token_name]: csrf_hash,
                mail_id: id,
                type: "sys"
            },
            beforeSend: function() {
                button.html('<i class="fa fa-fw fa-spinner fa-pulse"></i> ');
            }
        }).done(function(json) {
            handle_info_box(json.status, json.msg);
        }).fail(function(jqxhr) {
            message_ajax_fail_show(jqxhr);
        }).always(function() {
            button.html(or_text);
        });
    });
    ajax_inline_edit(site_url + 'back/Sendmail/editInline');
</script>