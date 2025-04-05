<div class="card-header"><?= $h4 ?></div>
<div class="card-body">
    <form method="POST" id="send">
        <div class="form-group">
            <input type="email" name="email" autofocus required placeholder="Emailadres..." class="form-control">
        </div>

        <div class="form-group">
            <input type="password" name="password" required placeholder="Wachtwoord..." class="form-control">
        </div>
        <div class="form-group">
            <?= add_csrf_value(); ?>
            <button type="submit" class="btn btn-primary btn-block" id="submit_button">Installeren</button>
        </div>
    </form>
</div>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>