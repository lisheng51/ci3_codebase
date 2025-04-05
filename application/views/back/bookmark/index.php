<?= link_tag(sys_asset_url("treetable/jquery.treegrid.css")); ?>
<?= script_tag(sys_asset_url("treetable/jquery.treegrid.min.js")); ?>
<?= script_tag(sys_asset_url("treetable/question_fix.js")); ?>

<form id="form_search">
    <?= add_csrf_value(); ?>
    <input type="hidden" name="view_mode" value="no_del" />
</form>

<div class="row">
    <div class="col-12" id="ajax_search_content">
        <?= $result; ?>
    </div>
</div>

<div class="modal fade" id="Modal_question_edit" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="myModalLabel">Wijzigen</div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="ajax_send_edit">
                    <div class="row">
                        <div class="col-12">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Naam*</label>
                                    <input type="text" class="form-control" maxlength="100" name="name" required id="edit_name" />
                                </div>
                            </div>

                            <div class="col-12">
                                <label>Icon</label>
                                <div class="form-group">
                                    <input type="text" maxlength="100" class="form-control" name="icon" id="edit_icon" />
                                </div>
                            </div>

                            <div class="col-12">
                                <label>Omschrijving</label>
                                <div class="form-group">
                                    <input type="text" maxlength="255" class="form-control" name="description" id="edit_description" />
                                </div>
                            </div>

                            <div class="col-12 forNotSort">
                                <div class="form-group">
                                    <label>Path*</label>
                                    <input type="text" class="form-control" maxlength="100" name="path" id="edit_path" />
                                </div>
                            </div>

                            <div class="col-12 forNotSort">
                                <label>Soort*</label>
                                <div class="form-group">
                                    <div id="edit_select_sort"></div>
                                </div>
                            </div>

                            <div class="col-12 forNotSort">
                                <label>Is extern*</label>
                                <div class="form-group">
                                    <div id="edit_is_extern"></div>
                                </div>
                            </div>
                            <div class="col-12 forNotSort">
                                <label>Open in nieuw venster*</label>
                                <div class="form-group">
                                    <div id="edit_open_new"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <input type="hidden" name="bookmark_id" id="edit_bookmark_id" />
                                <input type="hidden" name="is_sort" id="edit_is_sort" />
                                <input type="hidden" name="user_id" value="<?= $user_id ?>" />
                                <?= add_csrf_value(); ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Sluiten</button>
                <button class="btn btn-primary" type="button" id="edit_submit_button">Wijzigen</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="Modal_question_add" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="myModalLabel">Toevoegen</div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="ajax_send_add">
                    <div class="row">

                        <div class="col-12">
                            <div class="form-group">
                                <label>Naam*</label>
                                <input type="text" class="form-control" maxlength="100" name="name" required id="add_name" />
                            </div>
                        </div>

                        <div class="col-12">
                            <label>Icon</label>
                            <div class="form-group">
                                <input type="text" maxlength="100" class="form-control" name="icon" id="add_icon" />
                            </div>
                        </div>
                        <div class="col-12">
                            <label>Omschrijving</label>
                            <div class="form-group">
                                <input type="text" maxlength="255" class="form-control" name="description" id="add_description" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <?= add_csrf_value(); ?>
                                <input type="hidden" name="parent_bookmark_id" value="0" />
                                <input type="hidden" name="is_sort" value="1" />
                                <input type="hidden" name="open_new" value="0" />
                                <input type="hidden" name="is_extern" value="0" />
                                <input type="hidden" name="user_id" value="<?= $user_id ?>" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Sluiten</button>
                <button class="btn btn-primary" type="button" id="add_submit_button">Toevoegen</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="Modal_question_add_child" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="myModalLabel">Toevoegen aan <span id="q_name"></span></div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="ajax_send_add_child">
                    <div class="row">
                        <div class="col-12">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Naam*</label>
                                    <input type="text" class="form-control" maxlength="100" name="name" required id="add_child_name" />
                                </div>
                            </div>

                            <div class="col-12">
                                <label>Icon</label>
                                <div class="form-group">
                                    <input type="text" maxlength="100" class="form-control" name="icon" id="add_child_icon" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label>Omschrijving</label>
                                <div class="form-group">
                                    <input type="text" maxlength="255" class="form-control" name="description" id="add_child_description" />
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Path*</label>
                                    <input type="text" class="form-control" required maxlength="100" name="path" id="add_child_path" />
                                </div>
                            </div>

                            <div class="col-12">
                                <label>Is extern*</label>
                                <div class="form-group">
                                    <?= select_boolean('is_extern'); ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <label>Open in nieuw venster*</label>
                                <div class="form-group">
                                    <?= select_boolean('open_new'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <?= add_csrf_value(); ?>
                                    <input type="hidden" name="parent_bookmark_id" id="add_child_parent_bookmark_id" />
                                    <input type="hidden" name="is_sort" value="0" />
                                    <input type="hidden" name="user_id" value="<?= $user_id ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Sluiten</button>
                <button class="btn btn-primary" type="button" id="add_child_submit_button">Toevoegen</button>
            </div>
        </div>
    </div>
</div>




<!-- Modal back-->
<div class="modal fade" id="Modal_back_question" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="myModalLabel">Bevestig uw keuze</div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Weet u zeker dat u deze (<span class="back_content_info"></span>) wilt terugzetten?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary back_link">Ja</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Nee</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal delete-->
<div class="modal fade" id="Modal_delete_question" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="myModalLabel">Bevestig uw keuze</div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Weet u zeker dat u deze (<span class="remove_content_info"></span>) wilt verwijderen?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary del_link">Ja</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Nee</button>
            </div>
        </div>
    </div>
</div>

<script>
    handle_question_ajax_form_box_edit();
    handle_question_ajax_form_box_add();
    handle_question_ajax_form_box_add_child();
    handle_question_delete_box();
    handle_question_back_box();
    /**
     * Looks for children in list and re-attaches them directly after their parent.
     */
    function rebuildTree(parent) {
        var children = getAllChildren(parent, []);
        if (children.length > 0) {
            $(children).detach().insertAfter($(parent));
        }
    }

    /**
     * Get all children where TreeGrid's getChildNodes doesn't.
     */
    function getAllChildren(element, list) {
        var tree = getEntireTree(element, list);
        tree.shift();
        return tree;
    }

    /**
     * Gets the entire tree (including parent).
     */
    function getEntireTree(element, list) {
        var children = $(element).siblings('[data-f_question_id="' + $(element).attr('id') + '"]');
        list.push(element);
        children.each(function(index, child) {
            getEntireTree(child, list);
        });
        return list;
    }

    function saveTreeGridState() {
        // Go through all the rows, save current Collapsed state.
        $('tr', "tbody#itemContainer").each(function(index, currentRow) {
            $(currentRow).data('isCollapsedState', $(currentRow).treegrid('isCollapsed'));
        });
    }

    function restoreTreeGridState() {
        // Re-instantiate TreeGrid.
        $('.tree').treegrid({
            expanderExpandedClass: 'fas fa-minus',
            expanderCollapsedClass: 'fas fa-plus',
        });

        // Restore previous isCollapsedState for all other rows.
        $('tr', "tbody#itemContainer").each(function(index, currentRow) {
            // Checks for False-y values, will expand replaced row.
            if ($(currentRow).data('isCollapsedState')) {
                $(currentRow).treegrid('collapse');
                $(currentRow).removeData('isCollapsedState');
            }
        });
    }

    function handle_question_back_box() {
        $('#Modal_back_question').on('show.bs.modal', function(e) {
            var dellink = $(e.relatedTarget).data('del_link');
            var id = $(e.relatedTarget).data('search_data');
            var remove_content_info = $(e.relatedTarget).data('remove_content_info');
            $('span.back_content_info').html(remove_content_info);
            $("button.back_link").unbind().click(function() {
                $.ajax({
                    url: dellink,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        del_id: id,
                        [csrf_token_name]: csrf_hash
                    },
                    beforeSend: function() {
                        $("button.back_link").html(
                            '<i class="fa fa-spinner fa-pulse"></i>');
                        $("button.back_link").attr("disabled", "disabled");
                        $(e.relatedTarget).html('<i class="fa fa-spinner fa-pulse"></i> ');
                    }
                }).done(function(json) {
                    if (json.status === 'good') {
                        $(e.relatedTarget).replaceWith(json.change_button);
                        $("span#" + id + "_name_text_color").html(json.name_text_color);
                    }

                    $("button.back_link").text("Ja");
                    $("button.back_link").removeAttr("disabled");
                    $(e.relatedTarget).html('<i class="fas fa-recycle"></i> ');
                }).fail(function(jqxhr) {
                    message_ajax_fail_show(jqxhr);
                }).always(function() {
                    $('#Modal_back_question').modal('hide');
                });
            });
        });
    }

    function handle_question_delete_box() {
        $('#Modal_delete_question').on('show.bs.modal', function(e) {
            var dellink = $(e.relatedTarget).data('del_link');
            var id = $(e.relatedTarget).data('search_data');
            var remove_content_info = $(e.relatedTarget).data('remove_content_info');
            $('span.remove_content_info').html(remove_content_info);
            $('#Modal_delete_question').find('div.modal-body').html('Weet u zeker dat u deze (' +
                remove_content_info + ') wilt verwijderen?');
            $("button.del_link").unbind().click(function() {
                $.ajax({
                    url: dellink,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        del_id: id,
                        [csrf_token_name]: csrf_hash
                    },
                    beforeSend: function() {
                        $("button.del_link").html('<i class="fa fa-spinner fa-pulse"></i>');
                        $("button.del_link").attr("disabled", "disabled");
                        $(e.relatedTarget).html('<i class="fa fa-spinner fa-pulse"></i> ');
                    }
                }).done(function(json) {
                    if (json.status === 'good') {
                        $(e.relatedTarget).replaceWith(json.change_button);
                        $("span#" + id + "_name_text_color").html(json.name_text_color);
                        $('#Modal_delete_question').modal('hide');
                    }

                    if (json.status === 'error') {
                        $('#Modal_delete_question').find('div.modal-body').html(
                            '<span style=color:red>' + json.msg + '</span>');
                    }

                    //  Change invalid Yes/No options and add one-time listener to return to normal on modal close.
                    $("button.del_link").hide().next("button").text("Sluit venster");
                    $("#Modal_delete_question").one('hidden.bs.modal', function() {
                        $("button.del_link").show().next("button").text("Nee");
                    });

                    $("button.del_link").text("Ja");
                    $("button.del_link").removeAttr("disabled");
                    $(e.relatedTarget).html('<i class="fa-fw fas fa-times"></i> ');
                }).fail(function(jqxhr) {
                    message_ajax_fail_show(jqxhr);
                }).always(function() {
                    //$('#Modal_delete_question').modal('hide');
                });
            });
        });
    }

    function handle_question_ajax_form_box_add_child() {
        $('#Modal_question_add_child').on('show.bs.modal', function(e) {
            var ajaxurl = $(e.relatedTarget).data('add_child_link');
            var id = $(e.relatedTarget).data('search_data');
            var q_name = $(e.relatedTarget).data('search_name');
            $("span#q_name").html(q_name);

            $("input#add_child_name").val("");
            $("input#add_child_icon").val("");
            $("input#add_child_description").val("");
            $("input#add_child_path").val("");
            $("input#add_child_parent_bookmark_id").val(id);

            $("button#add_child_submit_button").unbind().click(function() {
                var senddata = $("form#ajax_send_add_child").serialize();
                $.ajax({
                    url: ajaxurl,
                    data: senddata,
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        $("button#add_child_submit_button").html(
                            'Verzenden.. <i class="fa fa-spinner fa-pulse"></i>');
                        $("button#add_child_submit_button").attr("disabled", "disabled");
                    }
                }).done(function(json) {
                    if (json.status === "good") {
                        $('#Modal_question_add_child').modal('hide');
                        //var child_div = 'tr.treegrid-' + id + ':last';
                        saveTreeGridState();
                        if ($('tr.treegrid-parent-' + id).length) {
                            var child_div = 'tr.treegrid-parent-' + id + ':last';
                        } else {
                            var child_div = 'tr.treegrid-' + id + ':last';
                        }

                        // Insert new row after last of its group.
                        $(child_div).after(json.ajax_edit_result);

                        // Find parent row and override existing isCollapsedState.
                        var parentID = $(child_div).data('f_question_id');
                        $('#' + parentID).data('isCollapsedState', false);

                        // Rebuild previous row in case its a parent row.
                        rebuildTree($(child_div).prev());

                        // Restore states
                        restoreTreeGridState();

                    } else {
                        handle_info_box(json.status, json.msg);
                    }
                }).always(function() {
                    $("button#add_child_submit_button").text('Toevoegen');
                    $("button#add_child_submit_button").removeAttr("disabled");
                }).fail(function(jqxhr) {
                    message_ajax_fail_show(jqxhr);
                });
            });
        });
    }



    function handle_question_ajax_form_box_add() {
        $('#Modal_question_add').on('show.bs.modal', function(e) {
            var ajaxurl = $(e.relatedTarget).data('add_link');
            $("input#add_name").val("");
            $("input#add_icon").val("");
            $("input#add_description").val("");
            $("button#add_submit_button").unbind().click(function() {
                var senddata = $("form#ajax_send_add").serialize();
                $.ajax({
                    url: ajaxurl,
                    data: senddata,
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        $("button#add_submit_button").html(
                            'Verzenden.. <i class="fa fa-spinner fa-pulse"></i>');
                        $("button#add_submit_button").attr("disabled", "disabled");
                    }
                }).done(function(json) {
                    if (json.status === "good") {
                        $('#Modal_question_add').modal('hide');

                        saveTreeGridState();
                        $('tr:last').after(json.ajax_edit_result).data('isCollapsedState',
                            true);
                        restoreTreeGridState();

                    } else {
                        handle_info_box(json.status, json.msg);
                    }
                    event_result_box(json.status, json.msg);
                }).always(function() {
                    $("button#add_submit_button").text('Toevoegen');
                    $("button#add_submit_button").removeAttr("disabled");
                }).fail(function(jqxhr) {
                    message_ajax_fail_show(jqxhr);
                });
            });

        });
    }

    function handle_question_ajax_form_box_edit() {
        $('#Modal_question_edit').on('show.bs.modal', function(e) {
            var ajaxurl = $(e.relatedTarget).data('edit_link');
            var id = $(e.relatedTarget).data('search_data');

            $.getJSON(site_url + "back/Bookmark/getOne/" + id, function(data) {
                $("div#edit_is_extern").html(data.is_extern);
                $("div#edit_open_new").html(data.open_new);
                $("div#edit_select_sort").html("");

                $("div.forNotSort").hide();
                if (data.rsdb.parent_bookmark_id > 0) {
                    $("div.forNotSort").show();
                    $("div#edit_select_sort").html(data.select_sort);
                }

                $("input#edit_bookmark_id").val(data.rsdb.bookmark_id);
                $("input#edit_name").val(data.rsdb.name);
                $("input#edit_path").val(data.rsdb.path);
                $("input#edit_is_sort").val(data.rsdb.is_sort);
                $("input#edit_description").val(data.rsdb.description);
                $("input#edit_icon").val(data.rsdb.icon);

                $("button#edit_submit_button").unbind().click(function() {
                    var senddata = $("form#ajax_send_edit").serialize();
                    $.ajax({
                        url: ajaxurl,
                        data: senddata,
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function() {
                            $("button#edit_submit_button").html(
                                'Verzenden.. <i class="fa fa-spinner fa-pulse"></i>'
                            );
                            $("button#edit_submit_button").prop("disabled", true);
                        }
                    }).done(function(json) {
                        if (json.status === "good") {
                            $('#Modal_question_edit').modal('hide');
                            ajax_form_search($("form#form_search"));
                            // saveTreeGridState();
                            // var saveCRICS = $("tr#" + id + "").data('isCollapsedState');
                            // $("tr#" + id + "").replaceWith(json.ajax_edit_result);
                            // $("tr#" + id + "").data('isCollapsedState', saveCRICS);
                            // restoreTreeGridState();
                        } else {
                            handle_info_box(json.status, json.msg);
                        }
                    }).always(function() {
                        $("button#edit_submit_button").text('Wijzigen');
                        $("button#edit_submit_button").prop("disabled", false);
                    }).fail(function(jqxhr) {
                        message_ajax_fail_show(jqxhr);
                    });
                });
            });
        });
    }
</script>