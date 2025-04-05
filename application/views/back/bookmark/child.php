<?php foreach ($listdb as $value) : ?>
    <tr class="treegrid-<?= $value[BookMarkModel::$primaryKey]; ?> treegrid-parent-<?= $value["parent_bookmark_id"]; ?> child" id="<?= $value[BookMarkModel::$primaryKey]; ?>" data-f_question_id="<?= $value["parent_bookmark_id"]; ?>">
        <input type="hidden" name="sort_list[<?= $value[BookMarkModel::$primaryKey] ?>]" value="<?= $value['order_list'] ?>">
        <td><span id="<?= $value[BookMarkModel::$primaryKey]; ?>_name_text_color"><?= $value["name"]; ?></span></td>
        <td><?= $value["description"] ?></td>
        <td>
            <button type="button" class="btn btn-primary btn-sm" id="start_ajax_sort"><i class="fa fa-arrows-alt"></i></button>
            <button type="button" class="btn btn-primary btn-sm" data-search_data="<?= $value[BookMarkModel::$primaryKey]; ?>" data-edit_link="<?= $value["edit_link"] ?>" data-toggle="modal" data-target="#Modal_question_edit"><i class="fa fa-pencil-alt"></i></button>
            <?= $value["change_button"]; ?>
            <a class="btn btn-success btn-sm" href="<?= $value["url"] ?>"><?= lang('view_icon') ?></a>
        </td>
    </tr>
<?php endforeach; ?>