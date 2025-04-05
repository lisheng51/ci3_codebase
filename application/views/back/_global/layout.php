<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title ?></title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <?= $asset ?>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?= $navbar ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content" class="">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <?php if (intval(c_key('webapp_option_dark_mode')) > 0) : ?>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="darkSwitch">
                            <label class="custom-control-label" for="darkSwitch">Donkere modus</label>
                        </div>
                    <?php endif ?>

                    <!-- Topbar Search -->
                    <?php if (ModuleModel::isActive('search')) : ?>
                        <form action="<?= site_url('search/back/Home') ?>" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                            <div class="input-group">
                                <input required type="text" class="form-control bg-light border-0 small" placeholder="" name="keyword">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif ?>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <?php if (ModuleModel::isActive('search')) : ?>
                            <li class="nav-item dropdown no-arrow d-sm-none">
                                <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-search fa-fw"></i>
                                </a>
                                <!-- Dropdown - Messages -->
                                <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                                    <form action="<?= site_url('search/back/Home') ?>" class="form-inline mr-auto w-100 navbar-search">
                                        <div class="input-group">
                                            <input required type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-search fa-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </li>
                        <?php endif ?>

                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa fa-history fa-fw"></i>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Geschiedenis
                                </h6>

                                <?php foreach ($listdb_history_url as $value) : ?>
                                    <a class="dropdown-item d-flex align-items-center" href="<?= site_url($value["path"]) ?>">
                                        <div>
                                            <div class="small text-gray-500"><?= date_format(date_create($value["date"]), 'H:i:s') ?></div>
                                            <?= $value["title"] ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </li>

                        <!-- Nav Item - Messages -->
                        <?php if (PermissionModel::checkHas("Message.index") === true) : ?>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-envelope fa-fw"></i>
                                    <!-- Counter - Messages -->
                                    <span class="badge badge-danger badge-counter <?= $show_total_new_message ?>"><?= $total_new_message ?></span>
                                </a>
                                <!-- Dropdown - Messages -->
                                <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                                    <h6 class="dropdown-header">
                                        Bericht
                                    </h6>

                                    <?php foreach ($listdb_message as $value) : ?>
                                        <a class="dropdown-item d-flex align-items-center" href="<?= site_url($path_name . '/Message/view/' . $value["message_id"]) ?>">
                                            <div>
                                                <div class="text-truncate"><?= $value["title"] ?></div>
                                                <div class="small text-gray-500"><?= $value["date"] ?></div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                    <a class="dropdown-item text-center small text-gray-500" href="<?= site_url($path_name . '/Message') ?>">Meer...</a>
                                </div>
                            </li>
                        <?php endif ?>

                        <div class="topbar-divider d-none d-sm-block"></div>
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= $display_info ?></span>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?= site_url($path_name . '/User/profile') ?>">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profiel
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" id="Modal_logout">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Uitloggen
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?= $breadcrumb ?>
                    <!--                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                                                    <h1 class="h3 mb-0 text-gray-800"><?= $breadcrumbTitle ?></h1>
                                                </div>-->
                    <?= $event_result_box ?>
                    <?= $content ?>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            <!-- Footer -->
            <footer class="sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright <?= date('Y') ?> <?= ENVIRONMENT !== 'development' ? '' : '(Omgeving: ' . ENVIRONMENT . ')' . span_tooltip('Geen mailverkeer') ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top"><i class="fa-fw fas fa-angle-up"></i></a>
    <?= script_tag(sys_asset_url("js/sb-admin-2.js")); ?>

    <div class="modal fade" id="modal_user_pic" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">Afbeelding</div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="image-cropper">
                                <img class="img-responsive toshow">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="img-preview img-preview-sm"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <span class="btn btn-primary btn-file">
                        Kies een foto<input type="file" accept="image/jpeg,image/png" name="file" id="pic">
                    </span>
                    <button type="button" class="btn btn-danger rotate">Roteren</button>
                    <button type="button" class="btn btn-success save" data-imgid="0">Opslaan</button>
                    <button type="button" class="btn btn-info" data-dismiss="modal">Sluiten</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        crop_user_image();
        $("a#Modal_logout").click(function(e) {
            Swal.fire({
                title: 'Bevestig uw keuze',
                icon: 'question',
                text: "Wilt u uitlogen?",
                showCancelButton: true,
                cancelButtonText: "Nee",
                confirmButtonText: "Ja"
            }).then((result) => {
                if (result.value) {
                    window.location.href = "<?= login_url('/logout') ?>";
                }
            });
        });
    </script>
    <?= link_tag(sys_asset_url("css/darktheme.css"));
    echo script_tag(sys_asset_url("js/darktheme.js")); ?>
</body>

</html>