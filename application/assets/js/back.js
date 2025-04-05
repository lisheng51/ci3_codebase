$(function () {
    //handle_select();
    HrefPermissionCk("ul.hrefPermissionCk li.nav-item div.collapse a.collapse-item");
    $('[data-toggle="tooltip"]').tooltip();
    setup_tinymce(".tinymce");
    handle_delete_box();
    ajax_pagination();
    button_reset();

    $("form input").on("invalid", function () {
        var tab = $(this).closest('.tab-pane').attr('id');
        activaTab(tab);
    });

    //runUpdateSessionTime();
});


function runUpdateSessionTime() {
    setTimeout(updateSessionTime, 1000);
    $("#sessionmaxlivetime").click(function () {
        var submit_button = $(this);
        $.ajax({
            url: site_url + "ajax/Login/sessionmaxlivetime",
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
            }
        }).done(function (json) {
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
            }
            submit_button.html(json.timenow);
        }).always(function () {
        }).fail(function (jqxhr) {
            message_ajax_fail_show(jqxhr);
        });
    });
}

function updateSessionTime() {
    var $worked = $("#sessionmaxlivetime");
    var myTime = $worked.html();
    if (myTime !== '00:00:00') {
        var ss = myTime.split(":");
        var dt = new Date();
        dt.setHours(ss[0]);
        dt.setMinutes(ss[1]);
        dt.setSeconds(ss[2]);

        var dt2 = new Date(dt.valueOf() - 1000);
        var temp = dt2.toTimeString().split(" ");
        var ts = temp[0].split(":");
        var htmlvalue = ts[0] + ":" + ts[1] + ":" + ts[2];
        $worked.html(htmlvalue);
        setTimeout(updateSessionTime, 1000);
    }
}