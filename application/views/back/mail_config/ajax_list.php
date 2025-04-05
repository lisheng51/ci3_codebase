<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">  
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>SMTP server</th>
                        <th>SMTP gebruikersnaam</th>
                        <th>SMTP afzender naam</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>  
                            <td><?= $value["host"]; ?></td>
                            <td><?= $value["user"]; ?></td>
                            <td><?= $value["name"]; ?></td>
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