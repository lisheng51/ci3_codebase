<div class="card">
    <h6 class="card-header">Aantal gevonden : <span class="totalcount"><?= $total ?></span></h6>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Info</th>
                        <th>Datum & Tijd</th>
                        <th>API naam</th>
                        <th>IP</th>
                        <th>Path/url</th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><button class="btn btn-info btn-sm" data-toggle="collapse" data-target="#Info_<?= $value["log_id"]; ?>">Info</button> </td>
                            <td><?= F_datetime::convert_datetime($value["datetime"]) ?></td>
                            <td><?= $value["name"] ?></td>
                            <td><?= $value["ip_address"] ?></td>
                            <td><?= $value["path"] ?></td>
                        </tr>
                        <tr>
                            <td colspan="5">
                                <?php
                                $header_value = json_decode($value["header_value"]) ?? [];
                                $post_value = json_decode($value["post_value"]) ?? [];
                                $get_value = json_decode($value["get_value"]) ?? [];
                                $out_value = json_decode($value["out_value"] ?? "") ?? [];
                                $header_value_class = empty($header_value) == true ? "d-none" : "";
                                $post_value_class = empty($post_value) == true ? "d-none" : "";
                                $get_value_class = empty($get_value) == true ? "d-none" : "";
                                $out_value_class = empty($out_value) == true ? "d-none" : "";
                                ?>
                                <div class="collapse" id="Info_<?= $value["log_id"]; ?>">
                                    <div class="row text-break">
                                        <div class="col <?= $header_value_class ?>">
                                            <div class="card">
                                                <div class="card-header">
                                                    Header:
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <?php foreach ($header_value as $key => $item) : ?>
                                                            <div class="col-4 border-bottom">
                                                                <?= $key . ": "; ?>
                                                            </div>
                                                            <div class="col-8 border-bottom">
                                                                <?= $item; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col <?= $post_value_class ?>">
                                            <div class="card">
                                                <div class="card-header">
                                                    Post:
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <?php foreach ($post_value as $key => $item) : ?>
                                                            <div class="col-4 border-bottom">
                                                                <?= $key . ": "; ?>
                                                            </div>
                                                            <div class="col-8 border-bottom">
                                                                <?= $item; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col <?= $get_value_class ?>">
                                            <div class="card">
                                                <div class="card-header">
                                                    Get:
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <?php
                                                        foreach ($get_value as $key => $item) :
                                                        ?>
                                                            <div class="col-5 border-bottom">
                                                                <?= $key . ": "; ?>
                                                            </div>
                                                            <div class="col-7 border-bottom">
                                                                <?= $item; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col <?= $out_value_class ?>">
                                            <div class="card">
                                                <div class="card-header">
                                                    Out:
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <?php
                                                        foreach ($out_value as $key => $item) : ?>
                                                            <div class="col-5 border-bottom">
                                                                <?= $key . ": "; ?>
                                                            </div>
                                                            <div class="col-7 border-bottom">
                                                                <?= json_encode($item); ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
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