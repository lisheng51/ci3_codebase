<?php

class Mail_template_info extends Back_Controller
{

    protected $title_module = 'Mail template';

    #[DisplayAttribute('Mail template overzicht', 'Overzicht')]
    public function index()
    {
        $data["title"] = $this->title_module . ' overzicht';
        $moduleName = CIInput()->get('moduleName') ?? "";
        $data["listdb"] = MailTemplateModel::selectFolder($moduleName);
        $data['edit_url'] = $this->controller_url . '/edit/';
        if (empty($moduleName) === false) {
            $data['edit_url'] = $this->controller_url . '/editModule/' . $moduleName . '/';
            $data["title"] = $this->title_module . ' - ' . $moduleName . ' overzicht';
        }
        $data["selectModule"] = ModuleModel::selectModule($moduleName);
        $data["event_result_box"] = "";
        $this->view_layout("index", $data);
    }

    #[DisplayAttribute('Mail template in module wijzigen', '')]
    public function editModule(string $moduleName = "", string $langname = "")
    {
        if (CIInput()->post()) {
            $this->editModuleAction($moduleName, $langname);
        }
        $arrResult = MailTemplateModel::moduleFetchfolderFiles($langname, $moduleName);
        if (empty($arrResult) === true) {
            redirect($this->controller_url);
        }

        $listdb = $arrResult;
        $config = $ul = [];
        foreach ($listdb as $filename) {
            $configValue = MailTemplateModel::moduleFetchfolderFile($langname, $moduleName, $filename);
            if (empty($configValue) === true) {
                continue;
            }
            $ul[] = $filename;
            $config[][$filename] = $configValue;
        }
        $data["title"] = $this->title_module . ' - ' . $langname . ' - ' . $moduleName;
        $data['ul'] = $ul;
        $data['listdb'] = $config;
        $this->view_layout("edit", $data);
    }

    private function editModuleAction(string $moduleName = "", string $langname = "")
    {
        $postData = CIInput()->post();
        foreach ($postData as $filename => $data) {
            MailTemplateModel::moduleSaveFolderFile($data, $langname, $moduleName, $filename);
        }

        $json["msg"] = $this->title_module . " is bijgewerkt!";
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Mail template wijzigen', '')]
    public function edit(string $langname = "")
    {
        if (CIInput()->post()) {
            $this->editAction($langname);
        }
        $arrResult = MailTemplateModel::fetchfolderFiles($langname);
        if (empty($arrResult) === true) {
            redirect($this->controller_url);
        }

        $listdb = $arrResult;
        $config = $ul = [];
        foreach ($listdb as $filename) {
            $configValue = MailTemplateModel::fetchfolderFile($langname, $filename);
            if (empty($configValue) === true) {
                continue;
            }
            $ul[] = $filename;
            $config[][$filename] = $configValue;
        }
        if (empty($ul) === true) {
            redirect($this->controller_url);
        }
        $data["title"] = $this->title_module . ' - ' . $langname;
        $data['ul'] = $ul;
        $data['listdb'] = $config;
        $this->view_layout("edit", $data);
    }

    private function editAction(string $langname = "")
    {
        $postData = CIInput()->post();
        foreach ($postData as $filename => $data) {
            MailTemplateModel::saveFolderFile($data, $langname, $filename);
        }

        $json["msg"] = $this->title_module . " is bijgewerkt!";
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }
}
