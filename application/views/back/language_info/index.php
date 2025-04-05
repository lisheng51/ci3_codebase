<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><?= lang('search_box_header_text') ?></div>
            <div class="card-body">
                <form id="form_search">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Module</label>
                                <?= $selectModule ?>
                            </div> 
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?= search_button() ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><?= $title ?></div>
            <div class="card-body">
                <div class="table-responsive">  
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Language</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listdb as $value) : ?>
                                <tr>  
                                    <td><?= $value; ?></td>
                                    <td>
                                        <a href="<?= site_url($edit_url . $value) ?>" class="btn btn-info btn-sm"><?= lang("edit_icon") ?></a>
                                    </td> 
                                </tr>
                            <?php endforeach; ?>   
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>