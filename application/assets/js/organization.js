function auto_geo_data(tbodyId) {
    $("#" + tbodyId).on('click', '.auto_geo_data', function () {
        var submit_button = $(this);
        var submit_button_text = submit_button.html();
        var reqMinId = $(this).closest('tr').attr('id');
        var zipcode = $("#" + tbodyId + " tr#" + reqMinId + ' input[name="' + tbodyId + '_zipcode[]"]').val();
        var housenr = $("#" + tbodyId + " tr#" + reqMinId + ' input[name="' + tbodyId + '_housenr[]"]').val();

        var matches = zipcode.match(/([1-9]{1}[0-9]{3})([a-zA-Z]{2})$/);
        if (matches === null) {
            $("#" + tbodyId + " tr#" + reqMinId + ' input[name="' + tbodyId + '_zipcode[]"]').focus();
            return false;
        }

        if (housenr === '') {
            $("#" + tbodyId + " tr#" + reqMinId + ' input[name="' + tbodyId + '_housenr[]"]').focus();
            return false;
        }

        $.ajax({
            url: site_url + "ajax/Address/data",
            type: 'POST',
            dataType: 'json',
            data: { zipcode: zipcode, housenr: housenr, [csrf_token_name]: csrf_hash },
            beforeSend: function () {
                submit_button.prop("disabled", true);
                submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
            }
        }).done(function (json) {
            if (json.status === 'error') {
                handle_info_box(json.status, json.msg);
            } else {
                $("#" + tbodyId + " tr#" + reqMinId + ' input[name="' + tbodyId + '_city[]"]').val(json.city);
                $("#" + tbodyId + " tr#" + reqMinId + ' input[name="' + tbodyId + '_street[]"]').val(json.street);
                $("#" + tbodyId + " tr#" + reqMinId + ' input[name="' + tbodyId + '_province[]"]').val(json.province);
            }
        }).always(function () {
            submit_button.html(submit_button_text);
            submit_button.prop("disabled", false);
        }).fail(function (jqxhr) {
            message_ajax_fail_show(jqxhr);
        });
    });
}

function multiAddById(addBtnId, tbodyId) {
    var reqMinId = 0;
    var cindex = $("#" + tbodyId + " tr:first").attr('id');
    $('button#' + addBtnId).click(function () {
        var $clone = $("#" + tbodyId + " tr#" + reqMinId).clone(true);
        cindex++;
        $clone.find('input').val('');
        $clone.find('select').val('');
        $clone.find('.del-tr').data('del-id', "");
        $clone.attr('id', cindex);
        $("#" + tbodyId + " tr:last").after($clone);
    });

    var arrIds = [];
    $("#" + tbodyId).on('click', '.del-tr', function () {
        var removeID = $(this).closest('tr').attr('id');
        var delID = $(this).data('del-id');
        if (removeID > reqMinId) {
            $("#" + tbodyId + " tr#" + removeID + "").remove();
            if (delID) {
                arrIds.push(delID);
                $("#del_" + tbodyId).val(arrIds.join());
            }
        }
    });
}