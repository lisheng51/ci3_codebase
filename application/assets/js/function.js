// bootstrap-select 1.14.0-beta3 appends the label again on every 'refresh' / 'render' (and renders everything twice
// on a select that is not initialised yet), so rebuild the picker instead
(function ($) {
    if (!$ || !$.fn || !$.fn.selectpicker) return;
    const original = $.fn.selectpicker;
    const patched = function (option, ...args) {
        if (option === 'refresh' || option === 'render') {
            return this.each(function () {
                const $el = $(this);
                const picker = $el.data('selectpicker');
                if (picker) {
                    const options = picker.options;
                    original.call($el, 'destroy');
                    original.call($el, options);
                } else {
                    original.call($el);
                }
            });
        }
        return original.apply(this, arguments);
    };
    Object.assign(patched, original);
    $.fn.selectpicker = patched;
})(window.jQuery);

function checkIsMobile() {
    if (navigator.userAgent.indexOf("Mobile") > 0) {
        return true;
    }
    return false;
}

function myfetch(options = {}, url) {
    if (!url) url = window.location.href;
    return fetch(url, options)
        .then(response => {
            const contentDisposition = response.headers.get('content-disposition');
            if (contentDisposition && contentDisposition.includes('attachment')) {
                return response.blob().then(blob => {
                    const filename = contentDisposition.split(';')[1].split('=')[1].replace(/"/g, '');
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.setAttribute('download', filename);
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    return { downloaded: true };
                });
            }

            return response.text().then(text => {
                if (!response.ok) throw new Error(`HTTP ${response.status}: ${text}`);
                try {
                    return JSON.parse(text);
                } catch {
                    return text;
                }
            });
        })
        .catch(err => {
            if (options.silent === true) {
                throw err;
            }
            let htmlString = err.message;
            if (message_ajax_fail) {
                htmlString = message_ajax_fail;
            }
            let title = "<span class='text-danger'>Foutmelding</span>";
            Swal.fire({
                title: title,
                icon: 'error',
                html: htmlString,
                showCancelButton: false,
                cancelButtonText: "Nee",
                confirmButtonText: "OK"
            });
        });
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
                    let searchIDs = $(".chat_id_select:checkbox:checked").map(function () {
                        return $(this).val();
                    }).get();
                    let ajaxurl = site_url + path;
                    let senddata = {
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
        let currentEle = $(this);
        let editid = currentEle.data("edit-id");
        let field = currentEle.data("field");
        let value = currentEle.data('value');
        let newValue = value === 0 ? 1 : 0;
        let formData = new FormData();
        formData.set([csrf_token_name], csrf_hash);
        formData.set('editid', editid);
        formData.set('fieldvalue', newValue);
        formData.set('type', 'boolean');
        formData.set('field', field);

        myfetch({
            method: 'POST',
            body: formData
        }, ajaxurl).then(json => {
            if (json.status !== 'good') {
                handle_info_box(json.status, json.msg);
            }
            if (showBox && json.status === 'good') {
                handle_info_box(json.status, json.msg);
            }
            currentEle.data('value', newValue);
        });
    });
}



function ajax_inline_edit(ajaxurl, showBox = true, inputsize = 25) {

    $("tbody td").on('dblclick', '.inline_edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if ($('input.inline_edit_input').length > 0) return;

        let currentEle = $(this);
        let editid = currentEle.data("edit-id");
        let field = currentEle.data("field");
        let value = $(this).text();

        currentEle.html('<input size="' + inputsize + '" class="inline_edit_input" type="text" value="' + value + '" />');
        $("input.inline_edit_input").focus();
        $("input.inline_edit_input").keyup(function (event) {
            if (event.keyCode == 13) {
                let newValue = $("input.inline_edit_input").val().trim();
                if (value !== newValue) {
                    let formData = new FormData();
                    formData.set('editid', editid);
                    formData.set('field', field);
                    formData.set('fieldvalue', newValue);
                    formData.set([csrf_token_name], csrf_hash);


                    myfetch({
                        method: 'POST',
                        body: formData
                    }, ajaxurl).then(json => {
                        if (json.status !== 'good') {
                            handle_info_box(json.status, json.msg);
                        }

                        if (showBox && json.status == 'good') {
                            handle_info_box(json.status, json.msg);
                        }

                        if (json.status == 'good') {
                            currentEle.text(newValue);
                        }
                        currentEle.data('value', newValue);
                    });
                } else {
                    currentEle.text(newValue);
                }

            }

        });
    });
}

function ajax_sort(ajaxurl) {
    // voorkomt dat de browser de pagina scrolt als je de handle op een touchscreen vastpakt
    $('tbody#itemContainer button#start_ajax_sort').css('touch-action', 'none');
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
            let formData = new FormData();
            formData.set([csrf_token_name], csrf_hash);
            ul.find('>tr').each(function () {
                index++;
                $(this).find('input').val(index);
                let formkey = $(this).find('input').attr('name');
                formData.set(formkey, index);
            });

            myfetch({
                method: 'POST',
                body: formData
            }, ajaxurl).then(json => {

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
                let formData = new FormData();
                formData.set([csrf_token_name], csrf_hash);
                formData.set('del_id', del_id);

                myfetch({
                    method: 'POST',
                    body: formData
                }, del_url).then(json => {
                    if (json.status === 'good' && !json.redirect_url) {
                        $("#" + del_id).fadeOut('slow', function () {
                            $(this).remove();
                            let countnow = parseInt($("span.totalcount").text(), 10) || 0;
                            $('span.totalcount').text(countnow - 1);
                        });
                    }

                    if (json.type_done) {
                        switch (json.type_done) {
                            case 'redirect':
                                if (json.redirect_url) {
                                    window.location.href = json.redirect_url;
                                }
                                break;
                        }
                    }
                    handle_info_box(json.status, json.msg);
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

        let submit_button_text = submit_button.parent().parent().html();
        submit_button.parent().parent().addClass("disabled");
        submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
        myfetch({
            method: 'POST',
            body: form_data
        }, ajaxurl).then(json => {
            if (json.result) {
                $(contentid).html(json.result);
            } else {
                handle_info_box(json.status, json.msg);
            }
        }).finally(() => {
            submit_button.parent().parent().html(submit_button_text);
            submit_button.parent().parent().removeAttr("disabled");
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

function toFormData(item = {}) {
    if (item instanceof FormData) {
        return item;
    }
    let form_data = new FormData();
    if (typeof item === 'string') {
        new URLSearchParams(item).forEach((value, key) => form_data.append(key, value));
        return form_data;
    }
    for (var section in item) {
        if (item[section] instanceof Array) {
            item[section].forEach(value => form_data.append(section + '[]', value));
        } else {
            form_data.append(section, item[section]);
        }
    }
    return form_data;
}

function axios_search(ajaxurl, item, submit_button) {
    let form_data = toFormData(item);
    form_data.set([csrf_token_name], csrf_hash);

    ajax_search(ajaxurl, form_data, submit_button);
}

function ajax_search(ajaxurl, formData, submit_button, ajax_content_div = "div#ajax_search_content") {

    const submit_button_text = submit_button.html();
    submit_button.attr("disabled", "disabled");
    submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');

    myfetch({
        method: 'POST',
        body: formData
    }, ajaxurl).then(json => {
        if (json.type_done === 'redirect' && json.redirect_url) {
            window.location.href = json.redirect_url;
            return;
        }

        if (json.result) {
            $(ajax_content_div).html(json.result);
        } else {
            handle_info_box(json.status, json.msg);
        }

        event_result_box(json.status, json.msg);
    })
        .finally(() => {
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

function tinymceTheme() {
    let theme = null;
    try {
        theme = localStorage.getItem('theme');
    } catch (e) { }
    if (!theme) {
        theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    return theme === 'dark' ? { skin: 'oxide-dark', content_css: 'dark' } : {};
}

function setup_tinymce(select, height = 300, content_style = "") {
    tinymce.init({
        ...tinymceTheme(),
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

async function refreshTinymceFileUrl(base64, name, user_id = 0) {
    let formData = new FormData();
    formData.append('base64', base64);
    formData.append('name', name);
    formData.append('user_id', user_id);
    formData.append(csrf_token_name, csrf_hash);

    try {
        let response = await fetch(site_url + 'ajax/upload/tinymce', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Network error: ${response.status}`);
        }

        let json = await response.json();
        return json.result || "";
    } catch (error) {
        message_ajax_fail_show(error);
        return "";
    }
}


function setup_tinymce_noxss_clean(selector = '.tinymce_noxss_clean', height = 600, user_id = 0, content_style = "") {
    tinymce.init({
        ...tinymceTheme(),
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

                    (async () => {
                        let newpath = await refreshTinymceFileUrl(base64, file.name, user_id);
                        cb(newpath, {
                            title: file.name,
                            text: file.name
                        });
                    })();
                };
                reader.readAsDataURL(file);
            };
            input.click();
        }
    });
}

function setup_tinymce_noxss_clean_extended(selector = '.tinymce_noxss_clean', height = 600, user_id = 0, content_style = "") {
    tinymce.init({
        ...tinymceTheme(),
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
                    (async () => {
                        let newpath = await refreshTinymceFileUrl(base64, file.name, user_id);
                        cb(newpath, {
                            title: file.name,
                            text: file.name
                        });
                    })();
                };
                reader.readAsDataURL(file);
            };
            input.click();
        }
    });
}

function message_ajax_fail_show(jqxhr) {
    let htmlString;
    if (message_ajax_fail) {
        htmlString = message_ajax_fail;
    } else {
        var str = jqxhr.responseText ?? jqxhr.data;
        htmlString = str.replace(/<style\b[^<>]*>[\s\S]*?<\/style\s*>/gi, '');
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
            myfetch({
                method: 'POST',
                body: form_data
            }, ajaxurl).then(json => {
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
            });
        }
        return false;
    });

}
// jQuery UI (sortable/draggable) luistert alleen naar muis-events; vertaal touch naar muis
// zodat slepen ook op telefoon/tablet werkt (zelfde aanpak als jQuery UI Touch Punch).
(function ($) {
    if (!('ontouchstart' in window) || !$.ui || !$.ui.mouse) {
        return;
    }

    let touchHandled = false;
    let touchMoved = false;

    function simulateMouseEvent(event, type) {
        if (event.originalEvent.touches.length > 1) {
            return;
        }
        event.preventDefault();
        let touch = event.originalEvent.changedTouches[0];
        touch.target.dispatchEvent(new MouseEvent(type, {
            bubbles: true,
            cancelable: true,
            view: window,
            detail: 1,
            screenX: touch.screenX,
            screenY: touch.screenY,
            clientX: touch.clientX,
            clientY: touch.clientY,
            button: 0
        }));
    }

    let mouseProto = $.ui.mouse.prototype;
    let _mouseInit = mouseProto._mouseInit;
    let _mouseDestroy = mouseProto._mouseDestroy;

    mouseProto._touchStart = function (event) {
        // alleen overnemen als de widget dit element accepteert (bv. de sorteer-handle), anders blijft scrollen werken
        if (touchHandled || !this._mouseCapture(event.originalEvent.changedTouches[0])) {
            return;
        }
        touchHandled = true;
        touchMoved = false;
        simulateMouseEvent(event, 'mouseover');
        simulateMouseEvent(event, 'mousemove');
        simulateMouseEvent(event, 'mousedown');
    };

    mouseProto._touchMove = function (event) {
        if (!touchHandled) {
            return;
        }
        touchMoved = true;
        simulateMouseEvent(event, 'mousemove');
    };

    mouseProto._touchEnd = function (event) {
        if (!touchHandled) {
            return;
        }
        simulateMouseEvent(event, 'mouseup');
        simulateMouseEvent(event, 'mouseout');
        if (!touchMoved) {
            simulateMouseEvent(event, 'click');
        }
        touchHandled = false;
    };

    mouseProto._mouseInit = function () {
        this.element.on({
            touchstart: $.proxy(this, '_touchStart'),
            touchmove: $.proxy(this, '_touchMove'),
            touchend: $.proxy(this, '_touchEnd')
        });
        _mouseInit.call(this);
    };

    mouseProto._mouseDestroy = function () {
        this.element.off({
            touchstart: $.proxy(this, '_touchStart'),
            touchmove: $.proxy(this, '_touchMove'),
            touchend: $.proxy(this, '_touchEnd')
        });
        _mouseDestroy.call(this);
    };
})(jQuery);
