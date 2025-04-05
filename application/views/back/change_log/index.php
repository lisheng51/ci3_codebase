<?php foreach ($listdb as $value) : ?>
    <div class="card mb-3">
        <div class="card-header"> Versie <?= $value["name"] ?></div>
        <div class="card-body">
            <ul>
                <?php foreach ($value["info"] as $info) : ?>
                    <li><?= $info ?></li>
                <?php endforeach; ?>  
            </ul>
            <blockquote class="blockquote mb-0">
                <footer class="blockquote-footer">Release datum/tijd: <?= F_datetime::convert_datetime($value["created_at"]) ?></footer>
            </blockquote>
        </div>
    </div>
<?php endforeach; ?>  


<?= $pagination; ?>
