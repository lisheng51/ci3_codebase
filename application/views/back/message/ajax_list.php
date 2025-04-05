<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">  
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Datum & Tijd</th>
                        <th>Van -> Naar</th>
                        <th>Onderwerp</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr id="<?= $value["message_id"]; ?>">  
                            <td><?= $value["date"] ?></td>
                            <td><?= $value["from_user_name"] ?> -> <?= $value["to_user_name"] ?></td>
                            <td><?= $value["title"] ?></td>
                            <td><?= $value["viewButton"] ?></td> 
                        </tr>
                    <?php endforeach; ?>   
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <?= $pagination; ?>
    </div>
</div>