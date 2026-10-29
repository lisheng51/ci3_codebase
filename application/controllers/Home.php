<?php

class Home extends MX_Controller
{

    public function index()
    {
        if (LoginModel::userId() > 0) {
            redirect(AccessCheckModel::redirectUrl());
        }
        AccessCheckModel::startRedirect();
    }

    public function version()
    {
        exit(GlobalModel::lastVersion());
    }

    public function js(string $module = '')
    {
        CIOutput()->set_content_type('text/javascript')->set_output(F_asset::appVars($module));
    }
}
