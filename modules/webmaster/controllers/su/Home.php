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
        $url['snack'] = ModuleModel::copieLiveDataUrl('snack');
        $url['footprint'] = ModuleModel::copieLiveDataUrl('footprint');
        $url['web'] = ModuleModel::copieLiveDataUrl('web');
        $url['worklog'] = ModuleModel::copieLiveDataUrl('worklog');
        $url['secret'] = ModuleModel::copieLiveDataUrl('secret');
        foreach ($url as $module => $site_url) {
            $link =  '<a href="' . $site_url . '">' . $module . '</a><br><br>';
            echo $link;
        }
    }
}
