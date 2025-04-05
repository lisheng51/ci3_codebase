<div class="card">
    <div class="card-header"><?= $title ?></div>
    <div class="card-body">
        <form id="send">
            <div class="row">
                <div class="col-8">
                    <label>Naam*</label>
                    <div class="form-group">
                        <input type="text" class="form-control" maxlength="50" name="name" required value="<?= $rsdb["name"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-4">
                    <label>Type*</label>
                    <div class="form-group">
                        <?= PermissionGroupTypeModel::select($rsdb[PermissionGroupTypeModel::$primaryKey] ?? 1); ?>
                    </div>
                </div>
                <div class="col-12">
                    <ul class="nav nav-tabs">
                        <?php foreach ($permissions as $module => $listdb) : list($name, $toggleId) = explode("#_#", $module); ?>
                            <li class="nav-item"><a class="nav-link" href="#module_id_<?= $toggleId ?>" data-toggle="tab"><?= $name ?></a></li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content">
                        <?php foreach ($permissions as $module => $listdb) : list($name, $toggleId) = explode("#_#", $module); ?>
                            <div id="module_id_<?= $toggleId ?>" class="tab-pane mt-3">
                                <li id="root_list" style="list-style-type: none;">
                                    <?php foreach ($listdb as $value) : ?>
                                        <div>
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input class="custom-control-input label_module_id_<?= $toggleId ?>" type="checkbox" <?= $value['checkbox_value'] ?> name="permission_id[]" value="<?= $value['permission_id'] ?>" id="label_permission_id_<?= $value['permission_id'] ?>">
                                                <label class="custom-control-label" for="label_permission_id_<?= $value['permission_id'] ?>"><?= $value['checkbox_label'] ?> <a href="<?= site_url($value['debug_link']) ?>" class="badge badge-primary"><?= $value['debug_link'] ?></a></label>
                                            </div>
                                            <ul style="padding-left: 0;">
                                                <?= $value["show_child"]; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </li>
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" type="checkbox" id="all_label_module_id_<?= $toggleId ?>">
                                    <label class="custom-control-label" for="all_label_module_id_<?= $toggleId ?>"><strong>Alles (de)selecteren</strong></label>
                                </div>
                            </div>

                            <script>
                                $('#all_label_module_id_<?= $toggleId ?>').click(function() {
                                    if (this.checked) {
                                        $('.label_module_id_<?= $toggleId ?>').each(function() {
                                            this.checked = true;
                                        });
                                    } else {
                                        $('.label_module_id_<?= $toggleId ?>').each(function() {
                                            this.checked = false;
                                        });
                                    }
                                });
                            </script>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <input type="hidden" name="<?= PermissionGroupModel::$primaryKey ?>" value="<?= $rsdb[PermissionGroupModel::$primaryKey] ?? 0; ?>" />
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button($rsdb) ?>
                        <?= add_reset_button() ?>
                        <?= $delButton; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $("input.custom-control-input").on('change', function(e) {
        if ($(this).is(":checked") && $(this).closest("li").attr('id') != "root_list") {
            console.log("checked");
            recursively_Check(this);
        } else if (!$(this).is(":checked")) {
            console.log("unchecked");
            uncheck(this);
        }
    });

    function recursively_Check(element) {
        let first_ul = $(element).closest("ul");
        let second_li = $(first_ul).closest("li");
        var firstcheckbox = $(second_li).find("input").first();

        if ($(second_li).attr('id') == "root_list") {
            let first_div = $(first_ul).closest("div");
            firstcheckbox = $(first_div).find("input").first();
            $(firstcheckbox).prop("checked", true);
            return;
        }

        $(firstcheckbox).prop("checked", true);
        recursively_Check(firstcheckbox)
    }

    function uncheck(element) {
        let first_div = $(element).closest("div");
        let parent = $(first_div).parent();
        let chckboxes = $(parent).find("input");
        $(chckboxes).prop("checked", false);
        console.log(chckboxes);
    }

    $('.nav a:first').tab('show');
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>