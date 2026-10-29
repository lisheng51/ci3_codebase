$(function () {
    $("#show_password_string").click(function () {
        if ($("#password1").attr("type") === "password") {
            $("#password1").attr("type", "text");
        } else {
            $("#password1").attr("type", "password");
        }
    });

    $('input[name=password1]').keyup(function () {
        var pswd = $(this).val();
        var muchLower = (pswd.match(/[a-z]/g) || []).length;

        if (pswd.length < 8 || pswd.length > 32) {
            $('#length').removeClass('alert-success').addClass('alert-danger');
        } else {
            $('#length').removeClass('alert-danger').addClass('alert-success');
        }

        //validate lower
        if (muchLower > 4) {
            $('#lower').removeClass('alert-danger').addClass('alert-success');
        } else {
            $('#lower').removeClass('alert-success').addClass('alert-danger');
        }

        //validate upper
        if (pswd.match(/[A-Z]{1,32}/)) {
            $('#upper').removeClass('alert-danger').addClass('alert-success');
        } else {
            $('#upper').removeClass('alert-success').addClass('alert-danger');
        }

        //validate number
        if (pswd.match(/\d{1,32}/)) {
            $('#number').removeClass('alert-danger').addClass('alert-success');
        } else {
            $('#number').removeClass('alert-success').addClass('alert-danger');
        }

        //validate special
        if (pswd.match(/\W{1,32}/)) {
            $('#special').removeClass('alert-danger').addClass('alert-success');
        } else {
            $('#special').removeClass('alert-success').addClass('alert-danger');
        }
    });
});