<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">  
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Datum & Tijd</th>
                        <th>API naam</th>
                        <th>IP</th>
                        <th>Path/url</th>
                        <th>Post</th>
                        <th>Get</th>
                        <th>Header</th>
                        <th>Out</th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>  
                            <td><?= F_datetime::convert_datetime($value["datetime"]) ?></td>
                            <td><?= $value["name"] ?></td>
                            <td><?= $value["ip_address"] ?></td>
                            <td><?= $value["path"] ?></td>
                            <td><?= $value["post_value"] ?></td>
                            <td><?= $value["get_value"] ?></td>
                            <td><?= $value["header_value"] ?></td>
                            <td><?= $value["out_value"] ?></td>
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