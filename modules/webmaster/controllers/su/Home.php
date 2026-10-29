<?php

class Home extends Su_Controller
{

    public function index()
    {
        $domain = CIInput()->get('domain') ?? '';
        if (empty($domain)) {
            redirect('webmaster/su/home/index?domain=https://debug.uniekdating.nl/');
        }

        $url['core'] = ModuleModel::copieLiveDataUrl('', ['bc_api_log', 'bc_app_log', 'bc_history_url', 'bc_fail_check', 'bc_login_history', 'bc_send_mail', 'bc_send_mail_file', 'bc_visitor']);
        $url['article'] = ModuleModel::copieLiveDataUrl('article');
        $url['casino'] = ModuleModel::copieLiveDataUrl('casino');
        foreach ($url as $module => $site_url) {
            $link =  '<a href="' . $site_url . '">' . $module . '</a><br><br>';
            echo $link;
        }
    }
}
