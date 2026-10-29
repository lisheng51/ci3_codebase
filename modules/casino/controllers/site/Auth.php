<?php

class Auth extends Site_Controller
{
    protected $title_module = 'Casino';

    public function index()
    {
        redirect($this->module_path_name . '/auth/login');
    }

    public function login()
    {
        if (!empty(CasinoUserModel::current())) {
            redirect($this->module_path_name . '/home');
        }

        $data["title"] = CasinoLang::t('login_title');
        $data["clientId"] = CasinoUserModel::clientId();
        $data["googleUrl"] = site_url($this->module_path_name . '/auth/google');
        $data["cardLogin"] = CasinoConfigModel::bool('casino_card_login');
        $data["cardUrl"] = site_url($this->module_path_name . '/auth/card');
        $data["blocked"] = CIInput()->get('blocked') === '1';
        // a webmaster logged in to the back office can come in without Google
        $back = CasinoUserModel::backOfficeWebmaster();
        $data["backOfficeUrl"] = $back ? site_url($this->module_path_name . '/auth/backoffice') : '';
        $data["backOfficeName"] = $back ? trim((string)($back['display_info'] ?? '')) ?: (string)($back['emailaddress'] ?? '') : '';
        $this->view_layout("login", $data);
    }

    /** Login with the back-office session of a webmaster (after logging out of the casino on purpose). */
    public function backoffice()
    {
        $id = CasinoUserModel::loginFromBackOffice();
        if ($id < 0) {
            redirect($this->module_path_name . '/auth/login?blocked=1');
        }
        redirect($this->module_path_name . ($id > 0 ? '/home' : '/auth/login'));
    }

    public function google()
    {
        $userId = CasinoUserModel::loginWithGoogle((string)(CIInput()->post('credential') ?? ''));
        if ($userId <= 0) {
            $json["status"] = "error";
            $json["msg"] = $userId < 0 ? CasinoLang::t('login_blocked') : CasinoLang::t('login_failed');
            exit(json_encode($json));
        }

        $json["status"] = "good";
        $json["redirect"] = site_url($this->module_path_name . '/home');
        exit(json_encode($json));
    }

    /** Login with a scanned (or typed) Holland Casino membership card number. */
    public function card()
    {
        $userId = CasinoUserModel::loginWithCard((string)(CIInput()->post('card') ?? ''));
        if ($userId <= 0) {
            $json["status"] = "error";
            $json["msg"] = match ($userId) {
                -1 => CasinoLang::t('login_blocked'),
                -2 => CasinoLang::t('card_too_many'),
                default => CasinoLang::t('card_invalid'),
            };
            exit(json_encode($json));
        }

        $json["status"] = "good";
        $json["redirect"] = site_url($this->module_path_name . '/home');
        exit(json_encode($json));
    }

    /** Web app manifest: "add to home screen" on a tablet opens the casino full screen, without browser bars. */
    public function manifest()
    {
        $json = [
            'name' => CasinoLang::t('title'),
            'short_name' => CasinoLang::t('title'),
            'start_url' => site_url($this->module_path_name . '/home'),
            'scope' => site_url($this->module_path_name . '/'),
            'display' => 'fullscreen',
            'display_override' => ['fullscreen', 'standalone'],
            'background_color' => '#0b3d2e',
            'theme_color' => '#0b3d2e',
            'icons' => [['src' => site_url($this->module_path_name . '/auth/icon'), 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'any']],
        ];
        header('Content-Type: application/manifest+json; charset=utf-8');
        exit(json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** App icon for the home screen: a die on casino green. */
    public function icon()
    {
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=604800');
        $pips = '';
        foreach ([[176, 176], [256, 256], [336, 336], [336, 176], [176, 336]] as [$x, $y]) {
            $pips .= '<circle cx="' . $x . '" cy="' . $y . '" r="30" fill="#c0392b"/>';
        }
        exit('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><rect width="512" height="512" rx="96" fill="#0b3d2e"/>'
            . '<rect x="106" y="106" width="300" height="300" rx="56" fill="#f8f4e8" stroke="#f5c542" stroke-width="12"/>' . $pips . '</svg>');
    }

    public function logout()
    {
        CasinoUserModel::logout();
        redirect($this->module_path_name . '/auth/login');
    }
}
