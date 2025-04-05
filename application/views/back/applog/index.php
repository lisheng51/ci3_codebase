<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><?= lang('search_box_header_text') ?></div>
            <div class="card-body">
                <form method="POST" id="form_search">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Datum</label>
                                <input type="text" name="reportrange" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <?= labelSelectInput('Beschrijving', 'description') ?>
                        </div>
                        <div class="col-md-3">
                            <?= labelSelectInput('Path/url', 'path') ?>
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
                        <?= select_order_by(AppLogModel::$selectOrderBy); ?>
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

<!-- Modal info -->
<div class="modal fade" id="Modal_view_detail_app_log" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Details</div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-info">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Datum & Tijd:</label>
                                            <span id="Modal_view_detail_date"></span>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Naam:</label>
                                            <span id="Modal_view_detail_display_info"></span> <span id="Modal_view_detail_display_info"></span>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Beschrijving:</label>
                                            <span id="Modal_view_detail_description"></span>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Path/url:</label>
                                            <span id="Modal_view_detail_url"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal">Gezien</button>
            </div>
        </div>
    </div>
</div>


<script>
    $("form#form_search").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
    input_reportrange('input[name="reportrange"]');
    run_modal_view_detail_app_log();
    multiSelectDelBatch();

    function run_modal_view_detail_app_log() {
        $('#Modal_view_detail_app_log').on('show.bs.modal', function(e) {
            var url = $(e.relatedTarget).data('view_link');
            $.ajax({
                url: url,
                dataType: 'json',
                beforeSend: function() {
                    $(e.relatedTarget).html('<i class="fa fa-spinner fa-pulse"></i> ');
                    $('span#Modal_view_detail_id').html('<i class="fa fa-spinner fa-pulse"></i> ');
                    $('span#Modal_view_detail_date').html('<i class="fa fa-spinner fa-pulse"></i> ');
                    $('span#Modal_view_detail_display_info').html('<i class="fa fa-spinner fa-pulse"></i> ');
                    $('span#Modal_view_detail_description').html('<i class="fa fa-spinner fa-pulse"></i> ');
                    $('span#Modal_view_detail_url').html('<i class="fa fa-spinner fa-pulse"></i> ');
                }
            }).done(function(json) {
                $('span#Modal_view_detail_id').text(json.log_id);
                $('span#Modal_view_detail_date').text(json.date);
                $('span#Modal_view_detail_display_info').text(json.display_info);
                $('span#Modal_view_detail_description').text(json.description);
                $('span#Modal_view_detail_url').text(json.path);
                $(e.relatedTarget).html('<i class="fa fa-eye"></i> ');
            }).fail(function(jqxhr) {
                message_ajax_fail_show(jqxhr);
            });
        });
    }
</script>