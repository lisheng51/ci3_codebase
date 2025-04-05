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
                        <th>Display</th>
                        <th>Gebruikersnaam</th>
                        <th>IP</th>
                        <th>Browser</th>
                        <th>Besturing systeem</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr id="<?= $value["login_history_id"]; ?>">
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input chat_id_select" type="checkbox" id="log_id<?= $value['login_history_id'] ?>" name='ids[]' class="" value="<?= $value['login_history_id'] ?>">
                                    <label class="custom-control-label font-weight-normal" for="log_id<?= $value['login_history_id'] ?>"><?= $value["date"] ?></label>
                                </div>
                            </td>
                            <td><?= $value["display_info"] ?></td>
                            <td><?= $value["username"] ?></td>
                            <td><?= $value["ip_address"] ?></td>
                            <td><?= $value["browser"] ?></td>
                            <td><?= $value["platform"] ?></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm delButton" data-search_data="<?= $value["login_history_id"]; ?>" data-del_link="<?= $value["del_url"] ?>"><?= lang("del_icon") ?></button>
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