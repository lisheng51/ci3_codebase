<div class="card-header"><?= $h4 ?></div>
<div class="card-body">
    <form method="POST" id="send">
        <div class="mb-3">
            <input type="email" name="email" autofocus required placeholder="Emailadres..." class="form-control">
        </div>

        <div class="mb-3">
            <input type="password" name="password" required placeholder="Wachtwoord..." class="form-control">
        </div>
        <div class="mb-3">
            <?= add_csrf_value(); ?>
            <button type="submit" class="btn btn-primary w-100" id="submit_button">Installeren</button>
        </div>
    </form>
</div>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>