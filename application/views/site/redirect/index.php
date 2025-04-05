<script>
    var width = 0;
    var width_sec = 100 /<?= $sec ?>;
    function doSomething() {
        width = width + width_sec;
        if (width > 100) {
            window.location = "<?= $url ?>";
        } else {
            $(".progress-bar").css('width', width + "%");
            $(".progress-bar").text(width.toFixed(0) + '%');
            setTimeout(doSomething, 1000);
        }
    }
    setTimeout(doSomething, 1000);
</script>  

<div class="card-header"><?php $title ?></div>
<div class="card-body">
    <div class="progress">
        <div class="progress-bar bg-info progress-bar-striped progress-bar-animated"></div>
    </div>
    <hr>
    <a class="btn btn-primary btn-block" href="<?= $url ?>"><?= lang('redirect_btn_txt') ?></a>
</div>