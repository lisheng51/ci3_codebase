<div class="card">
    <div class="card-header"><?= lang('search_box_header_text') ?></div>
    <div class="card-body">
        <form method="POST" id="form_search">
            <div class="row">
                <div class="col-3">
                    <?= labelSelectInput('Naam', 'path_description') ?>
                </div>
                <div class="col-3">
                    <label>Is actief</label>
                    <div class="form-group">
                        <?= select_boolean("is_active", 2, true) ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?= add_csrf_value(); ?>
                    <?= search_button() ?>
                    <?= reset_button() ?>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-2">
                <?= select_order_by(ModuleModel::$selectOrderBy, 'sort_list#asc'); ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12" id="ajax_search_content">
        <?= $result; ?>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Nieuw - Aantal: <span id="totalcountTodo"><?= $new["total"] ?></span></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Naam</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="itemContainerTodo">
                            <?php foreach ($new["listdb"] as $value) : ?>
                                <tr id="<?= $value["path"]; ?>">
                                    <td><?= $value["path_description"]; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm addmodule" data-search_data="<?= $value["path"]; ?>"><?= lang("add_icon") ?></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $("form#form_search").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });

    $('.addmodule').click(function() {
        let modulepath = $(this).data('search_data');
        let button = $(this);
        let data = {
            path: modulepath,
            [csrf_token_name]: csrf_hash
        };
        let submit_button_text = button.html();
        loadPermissionsort(button, data, submit_button_text, 'add/back');
        $("tbody#itemContainerTodo tr#" + modulepath + "").fadeOut('slow', function() {
            $(this).remove();
            let countnow = $("span#totalcountTodo").text();
            $('span#totalcountTodo').text(countnow - 1);
            ajax_form_search($("form#form_search"));
        });
    });

    function loadPermissionsort(button, data, submit_button_text, type = '') {
        $.ajax({
            url: site_url + 'back/Module/' + type,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function() {
                button.html('<i class="fa fa-fw fa-spinner fa-pulse"></i> ');
            }
        }).done(function(json) {
            if (type == 'add/back') {
                loadPermissionsort(button, data, submit_button_text, 'add/api')
            }
            if (type == 'update/back') {
                loadPermissionsort(button, data, submit_button_text, 'update/api')
            }
            handle_info_box(json.status, json.msg);
        }).fail(function(jqxhr) {
            message_ajax_fail_show(jqxhr);
        }).always(function() {
            button.html(submit_button_text);
        });
    }
</script>