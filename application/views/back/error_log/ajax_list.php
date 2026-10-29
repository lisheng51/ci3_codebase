<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Aantal</th>
                        <th>Datum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $key => $value) : ?>
                        <tr>
                            <td><button class="btn btn-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#order_id_<?= $key; ?>"><?= $value["count"] ?></button> </td>
                            <td><?= $value["date"] ?></td>
                            <td>
                                <?= $value["downloadButton"] ?>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3">
                                <ul class="list-group collapse" id="order_id_<?= $key ?>">
                                    <?php foreach ($value['listdb'] as $rs) : ?>
                                        <li class="list-group-item">
                                            <div class="card">
                                                <div class="card-header"><?= html_escape($rs['level']) ?> - <?= html_escape($rs['time']) ?></div>
                                                <div class="card-body">
                                                    <p class="text-break mb-0"><?= nl2br(html_escape($rs['message'])) ?></p>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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