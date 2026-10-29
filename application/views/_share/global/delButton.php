<button type="button" class="delButtonNoList btn btn-dark <?= $disabled ?>"><?= $text ?></button>
<script>
    $("button.delButtonNoList").click(function(e) {
        if ($(this).hasClass("disabled")) {
            return;
        }
        Swal.fire({
            title: 'Bevestig uw keuze',
            icon: 'question',
            text: "Weet u zeker dat u deze wilt verwijderen?",
            showCancelButton: true,
            cancelButtonText: "Nee",
            confirmButtonText: "Ja"
        }).then((result) => {
            if (result.value) {
                let formData = new FormData();
                formData.set([csrf_token_name], csrf_hash);
                formData.set('del_id', <?= $searchData ?>);

                myfetch({
                    method: 'POST',
                    body: formData
                }).then(json => {
                    if (json.type_done) {
                        switch (json.type_done) {
                            case 'redirect':
                                if (json.redirect_url) {
                                    window.location.href = json.redirect_url;
                                }
                                break;
                        }
                    }
                    handle_info_box(json.status, json.msg);
                });
            }
        });
    });
</script>