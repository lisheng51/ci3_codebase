<div class="card">
    <h5 class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?php echo $total ?></span></h5>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th class="d-none d-md-table-cell">Datum</th>
                        <th>Categorie</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td>
                                <a href="<?php echo $value["view_url"] ?>"><?php echo $value["title"]; ?></a>
                                <div class="small text-muted d-md-none"><?php echo $value["created_at"]; ?></div>
                            </td>
                            <td class="d-none d-md-table-cell text-nowrap"><?php echo $value["created_at"]; ?></td>
                            <td><?php echo $value["cate_name"] ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?php echo $value["edit_url"] ?>" class="btn btn-info btn-sm"><?php echo lang("edit_icon") ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <?php echo $pagination; ?>
    </div>
</div>