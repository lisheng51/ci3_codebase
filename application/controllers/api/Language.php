<?php

class Language extends API_Controller
{

    /**
     * @OA\Post(
     *     path="/api/language/list",
     *       tags={"core"},
     *     @OA\Response(response="200", description="An example resource")
     * )
     */
    public function list()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $arrResult = LanguageModel::selectList();
        ApiModel::outOK($arrResult, $apiLogId);
    }

    /**
     * @OA\Post(
     *     path="/api/language/file",
     *       tags={"core"},
     *     @OA\Response(response="200", description="An example resource")
     * )
     */
    public function file()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $language = CIInput()->post('language');
        $moduleName = CIInput()->post('module');
        $arrResult = [];

        if (empty($moduleName) === false && empty($language) === false) {
            $modulesmap = directory_map(FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR, 1);
            foreach ($modulesmap as $module) {
                $extension = get_mime_by_extension($module);
                if ($extension !== false) {
                    $arrResult[] = str_replace('_lang.php', '', $module);
                }
            }
            ApiModel::outOK($arrResult, $apiLogId);
        }

        if (empty($language) === false) {
            $modulesmap = directory_map(APPPATH . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR, 1);
            foreach ($modulesmap as $module) {
                $arrResult[] = str_replace('_lang.php', '', $module);
            }
            ApiModel::outOK($arrResult, $apiLogId);
        }

        ApiModel::outNOK(99, "Geen bestand gevonden", $apiLogId);
    }

    /**
     * @OA\Post(
     *     path="/api/language/index",
     *       tags={"core"},
     *     @OA\Response(response="200", description="An example resource")
     * )
     */
    public function index()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $language = CIInput()->post('language');
        $moduleName = CIInput()->post('module');
        $filename = CIInput()->post('file');
        $lang = [];
        if (empty($language) === false) {
            $data_file = APPPATH . "language/$language/" . $filename . '_lang.php';
            if (file_exists($data_file) === true) {
                require($data_file);
            }

            if (empty($moduleName) === false) {
                $ck_file_default = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $filename . '_lang.php';
                if (file_exists($ck_file_default) === true) {
                    require($ck_file_default);
                }
            }

            ApiModel::outOK($lang, $apiLogId);
        }

        ApiModel::outNOK(99, "Geen bestand gevonden", $apiLogId);
    }
}
