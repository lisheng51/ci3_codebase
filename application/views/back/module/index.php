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
                    <div class="mb-3">
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
        loadPermissionsort(button, modulepath);
        $("tbody#itemContainerTodo tr#" + modulepath + "").fadeOut('slow', function() {
            $(this).remove();
            let countnow = $("span#totalcountTodo").text();
            $('span#totalcountTodo').text(countnow - 1);
            ajax_form_search($("form#form_search"));
        });
    });

    function loadPermissionsort(submit_button, modulepath) {
        let submit_button_text = submit_button.html();
        submit_button.attr("disabled", "disabled");
        submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');

        let formData = new FormData();
        formData.set([csrf_token_name], csrf_hash);
        formData.set('modulepath', modulepath);

        myfetch({
                method: 'POST',
                body: formData
            }, site_url + 'back/Module/add').then(json => {
                if (json.status != 'good') {
                    handle_info_box(json.status, json.msg);
                    return;
                }
                return confirmSql(formData, json).then(moduleJson => {
                    formData.delete('confirm_sql[]');
                    return myfetch({
                        method: 'POST',
                        body: formData
                    }, site_url + 'back/Module/add/api').then(json => {
                        // The first call holds the table changes, show that message unless the api call failed
                        handle_info_box(json.status, json.status == 'good' ? moduleJson.msg : json.msg);
                    });
                });
            })
            .finally(() => {
                submit_button.html(submit_button_text);
                submit_button.removeAttr("disabled");
            });
    }

    // ENGINE changes and columns or indexes that are no longer in the sql files are only applied after confirmation
    function confirmSql(formData, json) {
        if (!json.confirm || json.confirm.length === 0) {
            return Promise.resolve(json);
        }
        let list = json.confirm.map(item => $('<div>').text(item.sql).html()).join('<br>');
        return Swal.fire({
            title: 'Bevestig uw keuze',
            icon: 'warning',
            html: 'Deze wijzigingen uitvoeren? Bij DROP gaat de data verloren, bij ENGINE wordt de tabel opnieuw opgebouwd.<br><br><small class="text-start d-block">' + list + '</small>',
            showCancelButton: true,
            cancelButtonText: "Nee",
            confirmButtonText: "Ja, uitvoeren"
        }).then((result) => {
            if (!result.value) {
                json.msg += '<br>' + json.confirm.length + ' wijziging(en) niet uitgevoerd.';
                return json;
            }
            json.confirm.forEach(item => formData.append('confirm_sql[]', item.key));
            return myfetch({
                method: 'POST',
                body: formData
            }, site_url + 'back/Module/add');
        });
    }
</script>