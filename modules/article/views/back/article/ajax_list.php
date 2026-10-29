<div class="card">
    <h5 class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?php echo $total ?></span></h5>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Datum</th>
                        <th>Categorie</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><a href="<?php echo $value["view_url"] ?>"><?php echo $value["title"]; ?></a></td>
                            <td><?php echo $value["created_at"]; ?></td>
                            <td><?php echo $value["cate_name"] ?></td>
                            <td>
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