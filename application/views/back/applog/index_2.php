<?= link_tag('//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css') . script_tag('//cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js') ?>
<div class="card">
    <div class="card-header">Resultaten</div>
    <div class="card-body">
        <div class="col-12" id="ajax_search_content_2">
            <table class="table table-striped table-bordered table-hover dt-responsive nowrap" id="dataTables-example">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Datum & Tijd</th>
                        <th>Gebruikersnaam</th>
                        <th>Beschrijving</th>
                        <th>Path/url</th>
                        <th></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th>
                        <th><input type="text" /></th>
                        <th><input type="text" /></th>
                        <th><input type="text" /></th>
                        <th><input type="text" /></th>
                        <th></th>
                    </tr>
                </tfoot>
                <tbody id="itemContainer"></tbody>
            </table>
        </div>
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
                                            <label>Log ID:</label>
                                            <span id="Modal_view_detail_id"></span>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Datum & Tijd:</label>
                                            <span id="Modal_view_detail_date"></span>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Naam:</label>
                                            <span id="Modal_view_detail_display_info"></span>
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
                <button type="button" class="btn btn-info" data-dismiss="modal">Gezien</button>
            </div>
        </div>
    </div>
</div>


<script>
    $("div#ajax_search_content_2").on('click', 'tr button.delButton', function(e) {
        var del_id = $(this).data('search_data');
        var del_url = $(this).data('del_link');
        Swal.fire({
            title: 'Bevestig uw keuze',
            icon: 'question',
            html: "Weet u zeker dat u deze wilt verwijderen?",
            showCancelButton: true,
            cancelButtonText: "Nee",
            confirmButtonText: "Ja"
        }).then((result) => {
            if (result.value) {
                var senddata = {
                    del_id: del_id,
                    [csrf_token_name]: csrf_hash
                };
                $.ajax({
                    url: del_url,
                    data: senddata,
                    type: 'POST',
                    dataType: 'json'
                }).done(function(json) {
                    if (json.status === 'good') {
                        $("#" + del_id + "").fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }
                    handle_info_box(json.status, json.msg);
                }).always(function() {

                }).fail(function(jqxhr) {
                    message_ajax_fail_show(jqxhr);
                });
            }
        });
    });


    $('tfoot').css("display", "table-header-group");
    var table = $('#dataTables-example').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        columnDefs: [{
            "targets": [0],
            "visible": false,
            "searchable": false
        }],
        order: [
            [1, 'desc']
        ],
        createdRow: function(row, data) {
            $(row).attr('id', data.log_id);
        },
        ajax: {
            url: site_url + "ajax/Applog/json_data",
            type: 'POST',
            data: {
                [csrf_token_name]: csrf_hash
            }
        },
        columns: [{
                "data": "log_id",
                "orderable": true
            },
            {
                "data": "date",
                "orderable": true
            },
            {
                "data": "emailaddress",
                "orderable": true
            },
            {
                "data": "description",
                "orderable": true
            },
            {
                "data": "path",
                "orderable": true
            },
            {
                "data": "button",
                "orderable": false
            }
        ],
        language: {
            "processing": 'Even geduld aub...',
            "search": "Filter: ",
            "lengthMenu": "_MENU_ items per pagaina",
            "zeroRecords": "Geen informatie beschikbaar!",
            "info": "Totaal _TOTAL_ items",
            "infoEmpty": "Geen items",
            "paginate": {
                "first": "Eerste",
                "last": "Laatste",
                "next": "&raquo",
                "previous": "&laquo"
            },
            "infoFiltered": "(gezocht in totaal _MAX_ items)"
        },
        initComplete: function() {
            this.api().columns().every(function() {
                var that = this;
                $('input', this.footer()).on('keyup', function() {
                    that.search(this.value).draw();
                });

                $('select', this.footer()).on('change', function() {
                    that.search(this.value).draw();
                });
            });
        }
    });
    $('.dataTables_filter').hide();
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
            $(e.relatedTarget).html('<i class="fa fa-eye fa-fw"></i> ');
        }).fail(function(jqxhr) {
            message_ajax_fail_show(jqxhr);
        });
    });
</script>