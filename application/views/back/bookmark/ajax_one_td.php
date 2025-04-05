<td><span id="<?= $bookmark_id; ?>_name_text_color"><?= $name; ?></span></td>
<td><?= $description; ?></td>
<td>
    <button type="button" class="btn btn-primary btn-sm" id="start_ajax_sort"><i class="fa fa-arrows-alt"></i></button>
    <button type="button" class="btn btn-primary btn-sm" data-search_data="<?= $bookmark_id ?>" data-edit_link="<?= $edit_link ?>" data-toggle="modal" data-target="#Modal_question_edit"><i class="fa fa-pencil-alt"></i></button>
    <?= $change_button; ?>
    <?= $add_child_link ?>
    <?= $add_view_link ?>
</td>