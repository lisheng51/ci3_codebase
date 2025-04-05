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
                                <label class="custom-control-label font-weight-normal" for="chat_id_selecctall"><strong>Datum & Tijd</strong></label>
                            </div>
                        </th>
                        <th>Gebruikersnaam</th>
                        <th>Beschrijving</th>
                        <th>Path/url</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr id="<?= $value["log_id"]; ?>">
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input chat_id_select" type="checkbox" id="log_id<?= $value['log_id'] ?>" name='log_id[]' class="" value="<?= $value['log_id'] ?>">
                                    <label class="custom-control-label font-weight-normal" for="log_id<?= $value['log_id'] ?>"><?= $value["date"] ?></label>
                                </div>
                            </td>
                            <td><?= $value["emailaddress"] ?></td>
                            <td><?= $value["description"] ?></td>
                            <td><?= $value["path"] ?></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm delButton" data-search_data="<?= $value["log_id"]; ?>" data-del_link="<?= $value["del_url"] ?>"><?= lang("del_icon") ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button type="button" data-path="<?= $ajax_batch_del_url ?>" class="btn btn-danger remove_chat_more">Verwijderen</button>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <div class="row">
            <div class="col-12">
                <?= $pagination; ?>
            </div>
        </div>
    </div>
</div>