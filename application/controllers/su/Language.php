<?php

class Language extends Su_Controller
{

    public function basic()
    {
        $arrResult = LanguageModel::selectList();
        $end = [];
        foreach ($arrResult as $language) {
            $modulesmap = directory_map(APPPATH  . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR, 1);
            $arrResultFiles = [];
            foreach ($modulesmap as $module) {
                $filename = str_replace('_lang.php', '', $module);
                $arrResultFiles[$filename] = $this->content($filename, $language);
            }

            $end[$language] = $arrResultFiles;
        }

        dump($end);
    }

    public function bestanden($language)
    {
        $arrResult = [];
        if (empty($language) === false) {
            $modulesmap = directory_map(APPPATH  . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR, 1);
            foreach ($modulesmap as $module) {
                $arrResult[] = str_replace('_lang.php', '', $module);
            }
            ApiModel::outOK($arrResult);
        }

        ApiModel::outNOK(99, "Geen bestand gevonden");
    }

    public function content($filename, $language)
    {
        $lang = [];
        if (empty($language) === false) {
            $data_file = APPPATH . "language/$language/" . $filename . '_lang.php';
            if (file_exists($data_file) === true) {
                require($data_file);
            }
        }

        return $lang;
    }
}
