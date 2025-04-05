<table class="table table-hover tree">
    <thead>
        <tr>
            <th width="40%"><button type="button" class="btn btn-success btn-sm" data-add_link="<?= $add_link ?>" data-toggle="modal" data-target="#Modal_question_add"><i class="fas fa-plus"></i></button> <label>Naam</label></th>
            <th width="30%"><label>Omschrijving</label></th>
            <th width="30%">
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" id="view_mode_checkbox" <?= $ckk; ?>>
                    <label class="custom-control-label font-weight-normal" for="view_mode_checkbox">Met archief</label>
                </div>
            </th>
        </tr>
    </thead>
    <tbody id="itemContainer">
        <?php foreach ($listdb as $value) : ?>

            <tr class="treegrid-<?= $value[BookMarkModel::$primaryKey]; ?> parent" id="<?= $value[BookMarkModel::$primaryKey]; ?>" data-f_question_id="<?= $value["parent_bookmark_id"]; ?>">
                <input type="hidden" name="sort_list[<?= $value[BookMarkModel::$primaryKey] ?>]" value="<?= $value['order_list'] ?>">
                <td><span id="<?= $value[BookMarkModel::$primaryKey]; ?>_name_text_color"><?= $value["name"]; ?></span></td>
                <td><?= $value["description"] ?></td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm" id="start_ajax_sort"><i class="fa fa-arrows-alt"></i></button>
                    <button type="button" class="btn btn-primary btn-sm" data-search_data="<?= $value[BookMarkModel::$primaryKey]; ?>" data-edit_link="<?= $value["edit_link"] ?>" data-toggle="modal" data-target="#Modal_question_edit"><i class="fa fa-pencil-alt"></i></button>
                    <?= $value["change_button"]; ?>
                    <?= $value["add_child_link"]; ?>
                </td>
            </tr>

            <?= $value["show_child"]; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    $('.tree').treegrid({
        //initialState: 'collapsed',
        expanderExpandedClass: 'fas fa-minus',
        expanderCollapsedClass: 'fas fa-plus'
    });
    treeSortList();
    $("input[type='checkbox']#view_mode_checkbox").click(function() {
        if ($(this).is(":checked")) {
            $('input[name="view_mode"]').val("all");
        } else {
            $('input[name="view_mode"]').val("no_del");
        }
        ajax_form_search($("form#form_search"));
    });
</script>