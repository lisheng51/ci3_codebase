<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input chat_id_select_all" type="checkbox" id="chat_id_selecctall">
                                <label class="custom-control-label" for="chat_id_selecctall">Datum & Tijd</label>
                            </div>
                        </th>
                        <th>Titel</th>
                        <th>Path/url</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr id="<?= $value["url_id"]; ?>">
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input chat_id_select" type="checkbox" id="log_id<?= $value['url_id'] ?>" name='ids[]' class="" value="<?= $value['url_id'] ?>">
                                    <label class="custom-control-label font-weight-normal" for="log_id<?= $value['url_id'] ?>"><?= $value["date"] ?></label>
                                </div>
                            </td>
                            <td><a href="<?= site_url($value["path"]) ?>"><?= $value["title"] ?></a></td>
                            <td><?= $value["path"] ?></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm delButton" data-search_data="<?= $value["url_id"]; ?>" data-del_link="<?= $value["del_url"] ?>"><?= lang("del_icon") ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <button type="button" data-path="<?= $ajax_batch_del_url ?>" class="btn btn-danger remove_chat_more">Verwijderen</button>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <div class="row">
            <div class="col-md-12">
                <?= $pagination; ?>
            </div>
        </div>
    </div>
</div>