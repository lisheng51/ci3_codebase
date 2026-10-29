<?php

class Config extends Back_Controller
{
    protected $title_module = 'Casino instelling';

    public function index()
    {
        if (CIInput()->post()) {
            $this->indexAction();
        }
        $values = [];
        foreach (array_keys(CasinoConfigModel::DEFAULTS) as $key) {
            $values[$key] = CasinoConfigModel::get($key);
        }
        $data["values"] = $values;
        $data["slotRtp"] = CasinoSlotModel::rtp();
        $data["slotJackpotOdds"] = array_product(array_map('count', CasinoSlotModel::REELS))
            / max(1, array_product(array_map(fn($reel) => count(array_keys($reel, 'seven', true)), CasinoSlotModel::REELS)));
        $data["event_result_box"] = "";
        $data["title"] = $this->title_module;
        $data["breadcrumbData"] = [["name" => $data["title"]]];
        $this->view_layout("index", $data);
    }

    private function indexAction()
    {
        $webdbs = CasinoConfigModel::postData(CIInput()->post());
        $old = [];
        foreach ($webdbs as $key => $value) {
            $old[$key] = CasinoConfigModel::get($key);
        }
        if (ConfigModel::update($webdbs)) {
            $changed = [];
            foreach ($webdbs as $key => $value) {
                if ((string)$old[$key] !== (string)$value) {
                    $changed[] = $key . ': ' . ($key === 'casino_google_client_id' ? '(gewijzigd)' : $old[$key] . ' > ' . $value);
                }
            }
            if ($changed) {
                CasinoLogModel::write(0, 'admin', 0, 'Instelling: ' . implode('; ', $changed));
            }
            $json["msg"] = $this->title_module . ' is bijgewerkt!';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
        $json["msg"] = $this->title_module . ' is niet bijgewerkt!';
        $json["status"] = "error";
        exit(json_encode($json));
    }
}
