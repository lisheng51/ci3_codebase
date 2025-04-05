<form enctype="multipart/form-data" method="POST">
    <div class="card">
        <div class="card-header">Algemeen</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Title*</label>
                        <input type="text" class="form-control" placeholder="Naam" name="title" />
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Bestand</label>
                        <input type="file" class="form-control" name="userFiles[]" multiple/>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button() ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>
