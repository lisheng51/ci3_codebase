<?php

echo F_asset::favicon();
echo F_asset::fontawesome();
echo F_asset::axios() . F_asset::jquery();
echo F_asset::jqueryUI();
echo F_asset::bootstrap();
echo F_asset::sweetalert2();
echo link_tag(sys_asset_url("css/checkbox_radio.css"));
echo link_tag(sys_asset_url("css/sb-admin-2.css"));
echo F_asset::moment();
echo F_asset::daterangepicker();
echo F_asset::selectpicker();
echo F_asset::cropper();
echo F_asset::tinymce();
echo script_tag(site_url("asset/index/" . $module_url));
echo script_tag(sys_asset_url("js/function.js"));
echo script_tag(sys_asset_url("js/back.js"));
echo ModuleModel::navbar($module_url, '_asset');
