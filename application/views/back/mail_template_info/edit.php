<script>
    $(function () {
        $("form#send").submit(function (e) {
            ajax_form_search($(this));
            e.preventDefault();
        });
    });
</script>

<ul class="nav nav-tabs">
    <?php
    foreach ($ul as $key => $value) :
        $status = $key === 0 ? "active" : null;
        ?>
        <li class="nav-item"><a class="nav-link <?= $status ?>" href="#<?= $value ?>" data-toggle="tab"><?= $value ?></a></li>
    <?php endforeach; ?>   
</ul>

<form method="POST" id="send">
    <div class="tab-content">
        <?php
        foreach ($listdb as $key => $value) :
            $status = $key === 0 ? "active" : null;
            foreach ($value as $did => $data):
                ?>
                <div id="<?= $did ?>" class="tab-pane <?= $status ?>">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <?php
                                foreach ($data as $key => $value):
                                    $inputnameShort = $did . '[' . $key . ']';
                                    $input = '<input type="text" name="' . $inputnameShort . '" class="form-control" value="' . stripslashes($value) . '"/>';
                                    if (substr($key, -5) === '_body') {
                                        $input = '<textarea name="' . $inputnameShort . '" class="form-control tinymce">' . stripslashes($value) . '</textarea>';
                                    }
                                    ?>
                                    <div class="col-12">
                                        <label><?= $key ?></label>
                                        <div class="form-group">
                                            <?= $input ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>   
                            </div>
                        </div>
                    </div>
                </div> 
                <?php
            endforeach;
        endforeach;
        ?>   
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <div class="form-group">
                <?= add_csrf_value(); ?>
                <?= add_submit_button('config') ?>
            </div>
        </div>
    </div>  
</form>