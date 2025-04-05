<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><?= lang('search_box_header_text') ?></div>
            <div class="card-body">
                <form method="POST" id="form_search">
                    <div class="row">
                        <div class="col-3">
                            <?= labelSelectInput('IP', 'ip_address') ?>
                        </div>

                        <div class="col-6">
                            <?= labelSelectInput('Path', 'path') ?>
                        </div>

                        <div class="col-3">
                            <?= labelSelectInput('Browser', 'browser') ?>
                        </div>

                        <div class="col-3">
                            <?= labelSelectInput('Platform', 'platform') ?>
                        </div>

                        <div class="col-3">
                            <div class="form-group">
                                <label>Datum</label>
                                <input type="text" name="reportrange" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?= add_csrf_value(); ?>
                            <?= search_button() ?>
                            <?= reset_button() ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-2">
                        <?= select_order_by(VisitorModel::$selectOrderBy) ?>
                    </div>
                    <div class="col-md-2">
                        <?= select_page_limit() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12" id="ajax_search_content">
        <?= $result; ?>
    </div>
</div>
<script>
    $("form#form_search").submit(function(e) {
        e.preventDefault();
        ajax_form_search($(this));
    });

    // $("form#form_search").submit(function(e) {
    //     e.preventDefault();
    //     let form_id = $(this);
    //     let senddata = new FormData(form_id[0]);
    //     let submit_button = form_id.find('button[type=submit]');
    //     let submit_button_text = submit_button.html();
    //     let ajaxurl = window.location.href;

    //     axios({
    //         method: 'post',
    //         url: ajaxurl,
    //         data: senddata,
    //         headers: {
    //             'Content-Type': false,
    //             'processData': false,
    //         },
    //         onUploadProgress: (progressEvent) => {
    //             submit_button.attr("disabled", "disabled");
    //             submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');
    //         }
    //     }).then((response) => {
    //         let json = response.data;
    //         Vue.createApp({
    //             data() {
    //                 return {
    //                     result: json.result
    //                 }
    //             }
    //         }).mount('#ajax_search_content')

    //     }).catch((error) => {
    //         let jqxhr = error.response;
    //         message_ajax_fail_show(jqxhr);
    //     }).then((response) => {
    //         submit_button.html(submit_button_text);
    //         submit_button.removeAttr("disabled");
    //     });

    // });
    input_reportrange('input[name="reportrange"]');
</script>