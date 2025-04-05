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

    public function js(string $module = '')
    {
        $output = "";
        $site_url = site_url('/');
        $module_url = site_url($module . '/');
        $session_id = session_id();
        $csrf_token_name = $this->security->get_csrf_token_name();
        $csrf_hash =  $this->security->get_csrf_hash();
        $maxUploadByte = maxUploadByte();
        $message_ajax_fail = ENVIRONMENT === 'development' ? '' : 'Er is momenteel storing, probeert u het later nog eens!';
        $output .= "var maxUploadByte = $maxUploadByte; var site_url = '$site_url'; var message_ajax_fail = '$message_ajax_fail'; var module_url = '$module_url';var session_id = '$session_id'; var csrf_hash = '$csrf_hash';var csrf_token_name = '$csrf_token_name'";
        CIOutput()->set_content_type('text/javascript')->set_output($output);
    }
}
