<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">  
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Bestand</th>
                        <th>Datum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr id="<?= $value["upload_id"]; ?>">  
                            <td><?= $value["title"]; ?></td>
                            <td><?= $value["type_name"]; ?></td>
                            <td><a href="<?= $value["path"]; ?>"><?= $value["file_name"]; ?></a></td>
                            <td><?= $value["created_at"]; ?></td>
                            <td><?= $value["editButton"] ?></td> 
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