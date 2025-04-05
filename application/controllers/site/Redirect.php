<?php

class Redirect extends Site_Controller
{
    public function index(int $sec = 0)
    {
        $redirect_url = CIInput()->get('redirect_url') ?? '';
        $url = site_url();
        if (!empty($redirect_url)) {
            $url = $redirect_url;
        }
        if ($sec <= 0) {
            redirect($url);
        }
        $data["url"] = $url;
        $data["sec"] = $sec;
        $data["title"] = lang('redirect_title');
        $this->view_layout("index", $data);
    }
}
