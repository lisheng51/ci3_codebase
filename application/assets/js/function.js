function checkIsMobile() {
    if (navigator.userAgent.indexOf("Mobile") > 0) {
        return true;
    }
    return false;

}

function multiSelectDelBatch(nocheckedmsg = "Geen item gekozen") {

    $("div#ajax_search_content").on('click', 'thead tr th input.chat_id_select_all', function () {
        if (this.checked) {
            $('.chat_id_select').each(function () {
                this.checked = true;
            });
        } else {
            $('.chat_id_select').each(function () {
                this.checked = false;
            });
        }
    });

    $("div#ajax_search_content").on('click', 'button.remove_chat_more', function () {
        if (!$('.chat_id_select').is(':checked')) {
            handle_info_box("error", nocheckedmsg);
        } else {
            let path = $(this).data("path");
            Swal.fire({
                title: 'Bevestig uw keuze',
                icon: 'question',
                text: "Weet u zeker dat u deze wilt verwijderen?",
                showCancelButton: true,
                cancelButtonText: "Nee",
                confirmButtonText: "Ja"
            }).then((result) => {
                if (result.value) {
                    var searchIDs = $(".chat_id_select:checkbox:checked").map(function () {
                        return $(this).val();
                    }).get();
                    var ajaxurl = site_url + path;
                    var senddata = {
                        'log_id': searchIDs
                    };
                    axios_search(ajaxurl, senddata, $("button.remove_chat_more"));
                }
            });
        }
    });
}

function getQuerystringNameValue(name) {
    let nameValue = null;
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has(name)) {
        nameValue = urlParams.get(name);
    }

    if (nameValue == null) {
        queryString = window.location.hash;
        let queryStringParamArray = queryString.split("#");
        for (var i = 0; i < queryStringParamArray.length; i++) {
            let queryStringNameValueArray = queryStringParamArray[i].split("=");
            if (name == queryStringNameValueArray[0]) {
                nameValue = queryStringNameValueArray[1];
            }
        }
    }
    return nameValue;
}

function handle_select(elename = 'select.selectpicker') {
    $(elename).select2({
        theme: "bootstrap-5",
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
    });
}


function ajax_inline_boolean(ajaxurl, showBox = true) {
    $("tbody td label").on('click', '.inline_boolean', function () {
        var currentEle = $(this);
        var editid = currentEle.data("edit-id");
        var field = currentEle.data("field");
        var value = currentEle.data('value');
        var newValue = value === 0 ? 1 : 0;
        let form_data = new FormData();
        form_data.set([csrf_token_name], csrf_hash);
        form_data.set('editid', editid);
        form_data.set('fieldvalue', newValue);
        form_data.set('type', 'boolean');
        form_data.set('field', field);
        axios({
            method: 'post',
            url: ajaxurl,
            data: form_data
        }).then((response) => {
            const json = response.data;
            if (json.status !== 'good') {
                handle_info_box(json.status, json.msg);
            }

            if (showBox === true && json.status == 'good') {
                handle_info_box(json.status, json.msg);
            }
        }).catch((error) => {
            const jqxhr = error.response;
            if (typeof jqxhr !== 'undefined') {
                message_ajax_fail_show(jqxhr);
            }
            console.log(error);
        }).then((response) => {
            currentEle.data('value', newValue);
        });
    });
}



function ajax_inline_edit(ajaxurl, showBox = true, inputsize = 25) {

    $("tbody td").on('dblclick', '.inline_edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if ($('input.inline_edit_input').length > 0) return;

        var currentEle = $(this);
        var editid = currentEle.data("edit-id");
        var field = currentEle.data("field");
        var value = $(this).text();

        currentEle.html('<input size="' + inputsize + '" class="inline_edit_input" type="text" value="' + value + '" />');
        $("input.inline_edit_input").focus();
        $("input.inline_edit_input").keyup(function (event) {
            if (event.keyCode == 13) {
                let newValue = $("input.inline_edit_input").val().trim();
                if (value !== newValue) {
                    let form_data = new FormData();
                    form_data.set('editid', editid);
                    form_data.set('field', field);
                    form_data.set('fieldvalue', newValue);
                    form_data.set([csrf_token_name], csrf_hash);

                    axios({
                        method: 'post',
                        url: ajaxurl,
                        data: form_data
                    }).then((response) => {
                        const json = response.data;
                        if (json.status !== 'good') {
                            handle_info_box(json.status, json.msg);
                        }

                        if (showBox === true && json.status == 'good') {
                            handle_info_box(json.status, json.msg);
                        }

                        if (json.status == 'good') {
                            currentEle.text(newValue);
                        }
                    }).catch((error) => {
                        const jqxhr = error.response;
                        if (typeof jqxhr !== 'undefined') {
                            message_ajax_fail_show(jqxhr);
                        }
                        console.log(error);
                    }).then((response) => {

                    });
                } else {
                    currentEle.text(newValue);
                }

            }

        });
    });
}

function ajax_sort(ajaxurl) {
    $('tbody#itemContainer').sortable({
        opacity: 0.6,
        revert: true,
        scroll: true,
        scrollSensitivity: 100,
        scrollSpeed: 100,
        items: "tr",
        handle: 'button#start_ajax_sort',
        cancel: '',
        cursor: 'move',
        helper: function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        },
        start: function (e, ui) { },
        stop: function (e, ui) { },
        update: function (e, ui) {
            let ul = $(ui.item).closest('tbody#itemContainer');
            let index = 0;
            let form_data = new FormData();
            form_data.set([csrf_token_name], csrf_hash);
            ul.find('>tr').each(function () {
                index++;
                $(this).find('input').val(index);
                let formkey = $(this).find('input').attr('name');
                form_data.set(formkey, index);
            });
            axios({
                method: 'post',
                url: ajaxurl,
                data: form_data
            }).then((response) => {

            }).catch((error) => {
                const jqxhr = error.response;
                if (typeof jqxhr !== 'undefined') {
                    message_ajax_fail_show(jqxhr);
                }
                console.log(error);
            }).then((response) => {
                //currentEle.data('value', newValue);
            });
        }
    });
}

function crop_user_image(exrapreviewimgid = '', aspectRatio = 1, modal = '#modal_user_pic', input = 'input#pic') {
    $(modal).on("shown.bs.modal", function (e) {
        let btn = $(e.relatedTarget);
        let effectid = btn.attr('id');
        let srcdata = btn.attr("src");
        let $image = $(".image-cropper > img");
        $image.cropper('destroy');
        $('img.toshow').attr("src", srcdata);
        $image.cropper({
            autoCropArea: 1,
            aspectRatio: aspectRatio,
            preview: ".img-preview",
        });
        $(modal + ' .save').data('imgid', effectid);
    });

    $(modal + ' .rotate').click(function () {
        let $image = $(".image-cropper > img");
        $image.cropper('rotate', 90);
    });

    $(modal + ' .save').click(function (e) {
        let $image = $(".image-cropper > img");
        let croppedCanvas = $image.cropper('getCroppedCanvas');
        let srcvalue = croppedCanvas.toDataURL();
        let effectid = $(this).data('imgid');
        $('img#' + effectid).attr("src", srcvalue);
        if (exrapreviewimgid != "") {
            $('img#' + exrapreviewimgid).attr("src", srcvalue);
        }
        $('input#' + effectid).val(srcvalue);
        $(modal).modal('toggle');
    });

    $(input).change(function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            let $image = $(".image-cropper > img");
            reader.readAsDataURL(this.files[0]);
            reader.onload = function (e) {
                $image.cropper("reset", true).cropper("replace", e.target.result);
            };
        }
    });
}


function input_reportrange_future(select) {
    $(select).daterangepicker({
        locale: {
            direction: 'ltr',
            format: 'DD-MM-YYYY',
            separator: ' t/m ',
            applyLabel: 'OK',
            cancelLabel: 'Annuleer',
            weekLabel: 'W',
            customRangeLabel: 'Of anders van',
            daysOfWeek: [
                "Zo",
                "Ma",
                "Di",
                "Wo",
                "Do",
                "Vr",
                "Za"
            ],
            monthNames: [
                "Jan",
                "Feb",
                "Maa",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Dec"
            ],
            //daysOfWeek: moment.weekdaysMin(),
            //monthNames: moment.monthsShort(),
            firstDay: moment.localeData().firstDayOfWeek()
        },
        ranges: {
            'Vandaag': [moment(), moment()],
            'Morgen': [moment().add(1, 'days'), moment().add(1, 'days')],
            'Komende 7 dagen': [moment(), moment().add(6, 'days')],
            'Komende 30 dagen': [moment(), moment().add(29, 'days')],
            'Deze maand': [moment().startOf('month'), moment().endOf('month')],
            'Volgende maand': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
            'Hele jaar': [moment().startOf('year'), moment().endOf('year')]
        }
    }, function (start, end) {
        $(select).val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
    });

    $(select).on('cancel.daterangepicker', function () {
        $(this).val('');
    });
    $(select).val("");
}

function input_reportrange(select) {
    $(select).daterangepicker({
        locale: {
            direction: 'ltr',
            format: 'DD-MM-YYYY',
            separator: ' t/m ',
            applyLabel: 'OK',
            cancelLabel: 'Annuleer',
            weekLabel: 'W',
            customRangeLabel: 'Of anders van',
            daysOfWeek: [
                "Zo",
                "Ma",
                "Di",
                "Wo",
                "Do",
                "Vr",
                "Za"
            ],
            monthNames: [
                "Jan",
                "Feb",
                "Maa",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Dec"
            ],
            //daysOfWeek: moment.weekdaysMin(),
            //monthNames: moment.monthsShort(),
            firstDay: moment.localeData().firstDayOfWeek()
        },
        ranges: {
            'Vandaag': [moment(), moment()],
            'Gisteren': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Afgelopen 7 dagen': [moment().subtract(6, 'days'), moment()],
            'Laatste 30 dagen': [moment().subtract(29, 'days'), moment()],
            'Deze maand': [moment().startOf('month'), moment().endOf('month')],
            'Vorige maand': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Hele jaar': [moment().startOf('year'), moment().endOf('year')]
        }
    }, function (start, end) {
        $(select).val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
    });

    $(select).on('cancel.daterangepicker', function () {
        $(this).val('');
    });
    $(select).val("");
}

function input_date(select, setOptions = {}) {
    var hasSetVal = ($(select).val());
    $(select).daterangepicker(
        Object.assign({
            showDropdowns: true,
            autoApply: true,
            showWeekNumbers: false,
            singleDatePicker: true,
            locale: {
                direction: 'ltr',
                format: 'DD-MM-YYYY',
                separator: ' t/m ',
                applyLabel: 'OK',
                cancelLabel: 'Annuleer',
                weekLabel: 'W',
                customRangeLabel: 'Of anders van',
                daysOfWeek: [
                    "Zo",
                    "Ma",
                    "Di",
                    "Wo",
                    "Do",
                    "Vr",
                    "Za"
                ],
                monthNames: [
                    "Jan",
                    "Feb",
                    "Maa",
                    "Apr",
                    "Mei",
                    "Jun",
                    "Jul",
                    "Aug",
                    "Sep",
                    "Okt",
                    "Nov",
                    "Dec"
                ],
                //daysOfWeek: moment.weekdaysMin(),
                //monthNames: moment.monthsShort(),
                firstDay: moment.localeData().firstDayOfWeek()
            }
        }, setOptions),
        function (start_date) {
            $(select).val(start_date.format('DD-MM-YYYY'));
        });
    $(select).val("");
    if (hasSetVal) {
        $(select).val(hasSetVal);
    }
    $(select).on('cancel.daterangepicker', function () {
        $(this).val('');
    });
}

function input_datewithtime(select, setOptions = {}) {
    var hasSetVal = ($(select).val());
    $(select).daterangepicker(
        Object.assign({
            //autoUpdateInput: true,
            singleDatePicker: true,
            timePicker: true,
            timePickerSeconds: true,
            timePicker24Hour: true,
            locale: {
                direction: 'ltr',
                format: 'DD-MM-YYYY HH:mm:ss',
                separator: ' t/m ',
                applyLabel: 'OK',
                cancelLabel: 'Annuleer',
                weekLabel: 'W',
                customRangeLabel: 'Of anders van',
                daysOfWeek: [
                    "Zo",
                    "Ma",
                    "Di",
                    "Wo",
                    "Do",
                    "Vr",
                    "Za"
                ],
                monthNames: [
                    "Jan",
                    "Feb",
                    "Maa",
                    "Apr",
                    "Mei",
                    "Jun",
                    "Jul",
                    "Aug",
                    "Sep",
                    "Okt",
                    "Nov",
                    "Dec"
                ],
                //daysOfWeek: moment.weekdaysMin(),
                //monthNames: moment.monthsShort(),
                firstDay: moment.localeData().firstDayOfWeek()
            }
        }, setOptions)
        , function (chosen_date) {
            $(select).val(chosen_date.format('DD-MM-YYYY HH:mm:ss'));
        });

    $(select).val("");
    if (hasSetVal) {
        $(select).val(hasSetVal);
    }

    $(select).on('cancel.daterangepicker', function () {
        $(this).val('');
    });
}
function button_reset() {
    $("button.reset").click(function () {
        $("form#form_search").find("select:not(.nochange),input[type=text], input[type=email]").val("");
        $("form#form_search").find('select.selectpicker').selectpicker('refresh');
        $('input:checkbox').prop('checked', false);
        ajax_form_search($("form#form_search"));
    });
}

function ajax_search_filter(select, selectname) {
    $("form#form_search select[name='" + selectname + "']").val($(select).data("search_data"));
    $("form#form_search").find("select[name='" + selectname + "']").selectpicker('refresh');
    ajax_form_search($("form#form_search"));
}

function handle_info_box(status, msg) {
    if (Swal.isVisible() && Swal.isLoading()) {
        Swal.hideLoading();
    }
    var title = "Informatie";
    switch (status) {
        case 'error':
            title = "<span class='text-danger'>Foutmelding</span>";
            Swal.fire({
                title: title,
                icon: 'error',
                html: msg,
                showCancelButton: false,
                cancelButtonText: "Nee",
                confirmButtonText: "OK",
            });
            break;
        case 'good':
            title = "<span class='text-primary'>Succes</span>";
            Swal.fire({
                title: title,
                icon: 'success',
                html: msg,
                showCancelButton: false,
                cancelButtonText: "Nee",
                confirmButtonText: "OK",
                timer: 3000
            });
            break;
        case 'waiting':
            title = "Even geduld aub...";
            Swal.fire({
                title: title,
                icon: 'warning',
                html: msg,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                backdrop: true
            });
            Swal.showLoading();
            break;
        case 'info':
            title = "<span class='text-info'>Melding</span>";
            Swal.fire({
                title: title,
                icon: 'info',
                html: msg,
                showCancelButton: false,
                cancelButtonText: "Nee",
                confirmButtonText: "OK"
            });
            break;
        default:
            break;
    }
}

function handle_delete_box(htmlText = "Weet u zeker dat u deze wilt verwijderen?") {
    $("div#ajax_search_content").on('click', 'tr button.delButton', function (e) {
        let del_id = $(this).data('search_data');
        let del_url = $(this).data('del_link');
        Swal.fire({
            title: 'Bevestig uw keuze',
            icon: 'question',
            html: htmlText,
            showCancelButton: true,
            cancelButtonText: "Nee",
            confirmButtonText: "Ja"
        }).then((result) => {
            if (result.value) {
                let form_data = new FormData();
                form_data.set([csrf_token_name], csrf_hash);
                form_data.set('del_id', del_id);
                axios({
                    method: 'post',
                    url: del_url,
                    data: form_data
                }).then((response) => {
                    const json = response.data;
                    if (json.status === 'good') {
                        $("#" + del_id + "").fadeOut('slow', function () {
                            $(this).remove();
                            var countnow = $("span.totalcount").text();
                            $('span.totalcount').text(countnow - 1);
                        });
                    }
                    handle_info_box(json.status, json.msg);
                }).catch((error) => {
                    const jqxhr = error.response;
                    if (typeof jqxhr !== 'undefined') {
                        message_ajax_fail_show(jqxhr);
                    }
                    console.log(error);
                }).then((response) => {
                    //submit_button.html(submit_button_text);
                });
            }
        });
    });
}

function ajax_form_search(form_id, ajaxurlcustomer) {
    let checkIfAction = "yes";
    if (typeof form_id.data('app-form-action') !== 'undefined') {
        checkIfAction = form_id.data('app-form-action');
    }

    if (checkIfAction === "yes") {
        let senddata = new FormData(form_id[0]);
        let page_limit = 0;
        let sql_orderby_field = "";
        if ($("select[name=page_limit]").length) {
            page_limit = $("select[name=page_limit]").val();
            senddata.append('page_limit', page_limit);
        }
        if ($("select[name=sql_orderby_field]").length) {
            sql_orderby_field = $("select[name=sql_orderby_field]").val();
            senddata.append('sql_orderby_field', sql_orderby_field);
        }

        let submit_button = form_id.find('button[type=submit]:focus');
        let ajaxurl = window.location.href;

        if (typeof form_id.data('app-target') !== 'undefined') {
            ajaxurl = form_id.data('app-target');
        }
        if (ajaxurlcustomer) {
            ajaxurl = ajaxurlcustomer;
        }
        ajax_search(ajaxurl, senddata, submit_button);
    }
}

function ajax_pagination(form_id = "form#form_search", contentid = "div#ajax_search_content", extraFormData = {}) {
    $(contentid).on('click', '.pagination a', function (e) {
        e.preventDefault();
        let ajaxurl = $(this).get(0).href;
        let form_data = new FormData($(form_id)[0]);
        let submit_button = $(this);
        let page_limit = 0;
        let sql_orderby_field = "";
        if ($("select[name=page_limit]").length) {
            page_limit = $("select[name=page_limit]").val();
            form_data.set('page_limit', page_limit);
        }
        if ($("select[name=sql_orderby_field]").length) {
            sql_orderby_field = $("select[name=sql_orderby_field]").val();
            form_data.set('sql_orderby_field', sql_orderby_field);
        }

        for (let section in extraFormData) {
            if (extraFormData[section] instanceof Array) {
                extraFormData[section].forEach(value => form_data.append(section + '[]', value));
            } else {
                form_data.append(section, extraFormData[section]);
            }
        }

        axios({
            method: 'post',
            url: ajaxurl,
            data: form_data,
            onUploadProgress: (progressEvent) => {
                submit_button.parent().parent().addClass("disabled");
                submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
            }
        }).then((response) => {
            const json = response.data;
            if (json.result) {
                $(contentid).html(json.result);
            } else {
                handle_info_box(json.status, json.msg);
            }
        }).catch((error) => {
            const jqxhr = error.response;
            if (typeof jqxhr !== 'undefined') {
                message_ajax_fail_show(jqxhr);
            }
            console.log(error);
        }).then((response) => {
            //submit_button.html(submit_button_text);
            submit_button.parent().parent().removeClass("disabled");
        });

    });

    $("select[name=sql_orderby_field]").change(function (e) {
        e.preventDefault();
        ajax_form_search($(form_id))
    });

    $("select[name=page_limit]").change(function (e) {
        e.preventDefault();
        ajax_form_search($(form_id))
    });
}

function axios_search(ajaxurl, item, submit_button) {
    let form_data = new FormData();
    form_data.set([csrf_token_name], csrf_hash);
    for (var section in item) {
        if (item[section] instanceof Array) {
            item[section].forEach(value => form_data.append(section + '[]', value));
        } else {
            form_data.append(section, item[section]);
        }
    }

    ajax_search(ajaxurl, form_data, submit_button);
}

function ajax_search(ajaxurl, senddata, submit_button, contentid = "div#ajax_search_content") {
    const submit_button_text = submit_button.html();
    axios({
        method: 'post',
        url: ajaxurl,
        data: senddata,
        headers: {
            'Content-Type': false,
            'processData': false,
        },
        onUploadProgress: (progressEvent) => {
            submit_button.attr("disabled", "disabled");
            submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
        }
    }).then((response) => {
        const headerval = response.headers['content-disposition'];
        if (typeof headerval !== 'undefined') {
            const filename = headerval.split(';')[1].split('=')[1].replace('"', '').replace('"', '');
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            link.remove();
        }

        const json = response.data;
        if (json.type_done) {
            switch (json.type_done) {
                case 'redirect':
                    if (json.redirect_url) {
                        window.location.href = json.redirect_url;
                    }
                    break;
                default:
                    break;
            }
        }
        if (json.result) {
            $(contentid).html(json.result);
        } else {
            handle_info_box(json.status, json.msg);
        }

        event_result_box(json.status, json.msg);
    }).catch((error) => {
        const jqxhr = error.response;
        if (typeof jqxhr !== 'undefined') {
            message_ajax_fail_show(jqxhr);
        }
        console.log(error);
    }).then((response) => {
        submit_button.html(submit_button_text);
        submit_button.removeAttr("disabled");
    });
}

function event_result_box(status, msg) {
    var title;
    switch (status) {
        case 'error':
            title = "Waarschuwing";
            break;
        case 'good':
            title = "Success";
            break;
        case 'waiting':
            title = "Even geduld aub...";
            break;
        default:
            break;
    }

    var oldheader = $('#event_result_box').find('.card-heading').html();
    var oldbody = $('#event_result_box').find('.card-body').html();

    $('#event_result_box').find('.card-heading').html(title);
    $('#event_result_box').find('.card-body').html(msg);

    if ($.trim($('#event_result_box').find('.card-body').html()).length) {
        setTimeout(function () {
            $('#event_result_box').find('.card-heading').html(oldheader);
            $('#event_result_box').find('.card-body').html(oldbody);
        }, 3000);
    }
}

function setup_tinymce(select, height = 300, content_style = "") {
    tinymce.init({
        selector: select,
        language_url: site_url + 'node_modules/tinymce-i18n/langs6/nl.js', // site absolute URL
        language: 'nl',
        branding: false,
        menubar: false,
        height: height,
        content_style: content_style,
        plugins: 'image code link lists',
        relative_urls: false,
        remove_script_host: false,
        statusbar: false,
        //document_base_url: site_url,
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect | code'
    });
}

function refreshTinymceFileUrl(base64, name, user_id = 0) {
    let newpath = "";
    let senddata = {
        base64: base64,
        name: name,
        user_id: user_id,
        [csrf_token_name]: csrf_hash
    };
    $.ajax({
        async: false,
        url: site_url + 'ajax/upload/tinymce',
        data: senddata,
        type: 'POST',
        dataType: 'json'
    }).done(function (json) {
        newpath = json.result;
    }).always(function () { }).fail(function (jqxhr) {
        message_ajax_fail_show(jqxhr);
    });

    // let form_data = new FormData();
    // form_data.set([csrf_token_name], csrf_hash);
    // form_data.set('base64', base64);
    // form_data.set('name', name);
    // form_data.set('user_id', user_id);

    // axios({
    //     method: 'post',
    //     url: site_url + 'ajax/upload/tinymce',
    //     data: form_data
    // }).then((response) => {
    //     const json = response.data;
    //     newpath = json.result;
    //     return newpath;
    // }).catch((error) => {
    //     const jqxhr = error.response;
    //     if (typeof jqxhr !== 'undefined') {
    //         message_ajax_fail_show(jqxhr);
    //     }
    //     console.log(error);
    // }).then((response) => {
    //     return newpath;
    // });
    return newpath;


}

function setup_tinymce_noxss_clean(selector = '.tinymce_noxss_clean', height = 600, user_id = 0, content_style = "") {
    tinymce.init({
        selector: selector,
        language_url: site_url + 'node_modules/tinymce-i18n/langs6/nl.js', // site absolute URL
        language: 'nl',
        branding: false,
        menubar: false,
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect | image code link',
        height: height,
        content_style: content_style,
        plugins: 'image code link lists',
        relative_urls: false,
        remove_script_host: false,
        paste_as_text: true,
        image_title: true,
        statusbar: false,
        file_picker_types: 'file image media',
        file_picker_callback: function (cb, value, meta) {
            let input = document.createElement('input');
            input.setAttribute('type', 'file');
            //input.setAttribute('accept', '.png, .jpg, .jpeg, .gif, .bmp');
            input.onchange = function () {
                let file = this.files[0];
                if (file.size > maxUploadByte) {
                    alert("Bestand is te groot");
                    //handle_info_box('info', "Bestand is te groot");
                    return false;
                }
                let reader = new FileReader();
                reader.onload = function () {
                    let base64 = reader.result.split(',')[1];
                    let newPath = refreshTinymceFileUrl(base64, file.name, user_id);
                    cb(newPath, {
                        title: file.name,
                        text: file.name
                    });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        }
    });
}

function setup_tinymce_noxss_clean_extended(selector = '.tinymce_noxss_clean', height = 600, user_id = 0, content_style = "") {
    tinymce.init({
        selector: selector,
        plugins: 'image code link lists',
        language_url: site_url + 'node_modules/tinymce-i18n/langs6/nl.js', // site absolute URL
        language: 'nl',
        branding: false,
        height: height,
        menubar: 'file edit view insert format tools table help',
        toolbar: 'undo redo | bold italic underline strikethrough | h4 h5 table blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor |  print link anchor code removeformat',
        autosave_ask_before_unload: true,
        color_map: [
            "12a12c", "Primary Green",
            "e42f7e", "Primary Pink",
        ],
        color_cols: "1",
        content_style: content_style,
        relative_urls: false,
        remove_script_host: false,
        paste_as_text: true,
        image_title: true,
        statusbar: false,
        file_picker_types: 'file image media',
        content_style: content_style,
        file_picker_callback: function (cb, value, meta) {
            let input = document.createElement('input');
            input.setAttribute('type', 'file');
            //input.setAttribute('accept', '.png, .jpg, .jpeg, .gif, .bmp');
            input.onchange = function () {
                let file = this.files[0];
                if (file.size > maxUploadByte) {
                    alert("Bestand is te groot");
                    //handle_info_box('info', "Bestand is te groot");
                    return false;
                }
                let reader = new FileReader();
                reader.onload = function () {
                    let base64 = reader.result.split(',')[1];
                    let newPath = refreshTinymceFileUrl(base64, file.name, user_id);
                    cb(newPath, {
                        title: file.name,
                        text: file.name
                    });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        }
    });
}

function message_ajax_fail_show(jqxhr) {
    let htmlString = "";
    if (jqxhr.hasOwnProperty('responseText')) {
        let str = jqxhr.responseText;
        htmlString = str.replace(/<style\b[^<>]*>[\s\S]*?<\/style\s*>/gi, '');
    }

    if (jqxhr.hasOwnProperty('data')) {
        let str = jqxhr.data;
        htmlString = str.replace(/<style\b[^<>]*>[\s\S]*?<\/style\s*>/gi, '');
    }

    if (message_ajax_fail) {
        htmlString = message_ajax_fail;
    }
    var title = "<span class='text-danger'>Foutmelding - " + jqxhr.status + " " + jqxhr.statusText + "</span>";
    Swal.fire({
        title: title,
        icon: 'error',
        html: htmlString,
        showCancelButton: false,
        cancelButtonText: "Nee",
        confirmButtonText: "OK"
    });
}


function activaTab(tab) {
    $('.nav-tabs a[href="#' + tab + '"]').tab('show');
    $('.navbar-nav a[href="#' + tab + '"]').tab('show');
}

function HrefPermissionCk(select) {
    $(select).click(function () {
        let redirectUrl = $(this).attr('href');
        let pathArray = redirectUrl.split(site_url);
        let pathurl = pathArray[1];
        if (pathurl) {
            let form_data = new FormData();
            form_data.set([csrf_token_name], csrf_hash);
            form_data.set('pathurl', pathurl);
            axios({
                method: 'post',
                url: site_url + 'ajax/Permission/checkForUser',
                data: form_data
            }).then((response) => {
                const json = response.data;
                if (json.type_done) {
                    switch (json.type_done) {
                        case 'redirect':
                            if (json.redirect_url) {
                                window.location.href = json.redirect_url;
                            }
                            break;
                        default:
                            break;
                    }
                }
                if (json.status === 'error') {
                    handle_info_box(json.status, json.msg);
                } else {
                    window.location.href = redirectUrl;
                }
            }).catch((error) => {
                const jqxhr = error.response;
                if (typeof jqxhr !== 'undefined') {
                    message_ajax_fail_show(jqxhr);
                }
                console.log(error);
            }).then((response) => {

            });
        }
        return false;
    });

}