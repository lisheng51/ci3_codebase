$(function () {
    send_form();
    $('[data-bs-toggle="tooltip"]').each(function () { new bootstrap.Tooltip(this); });
});


function send_form() {
    $("form#send").submit(function (e) {
        e.preventDefault();
        let submit_button = $(this).find('button[type=submit]');
        let submit_button_text = submit_button.html();

        // let submit_button = $(this).find('button[type=submit]:focus');
        // let submit_button_text = submit_button.html();

        // if (typeof submit_button_text == 'undefined') {

        //     submit_button = $(this).find('button[type=submit]');
        //     submit_button_text = submit_button.html();
        // }


        if ($('input#_core_access_code').length) {
            if ($('#_core_access_code').val().length === 0) {
                handle_info_box("error", "Uw toegangscode is leeg");
                return false;
            }
        }


        let formData = new FormData($(this)[0]);
        // if (typeof submit_button.data('extra-value') !== 'undefined') {
        //     formData.append('extra-mail', submit_button.data('extra-value'));
        // }

        // for (var pair of formData.entries()) {
        //     console.log(pair[0] + ' => ' + pair[1]);
        // }
        submit_button.attr("disabled", "disabled");
        submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
        myfetch({
            method: 'POST',
            body: formData
        }).then(json => {
            if (!json) return;
            if (json.type_done) {
                switch (json.type_done) {
                    case 'redirect':
                        if (json.redirect_url) {
                            window.location.href = json.redirect_url;
                        }
                        break;
                    case '2fa':
                        $('#2fa_block').removeClass("d-none");
                        $('input[name=_core_one_code]').prop("required", true);
                        break;
                    case 'show_access_code_input':
                        if (json.input_html) {
                            $('.show_access_code_input').html(json.input_html);
                        }
                        break;
                    default:
                        break;
                }
            }
            handle_info_box(json.status, json.msg);
        }).finally(() => {
            submit_button.html(submit_button_text);
            submit_button.removeAttr("disabled");
        });
    });
}