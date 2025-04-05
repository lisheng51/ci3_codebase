<?php

class Whitelist extends API_Controller
{

    private function whitelistModule(string $module = "")
    {
        $module = rtrim($module, DIRECTORY_SEPARATOR);
        $array = directory_map(FCPATH . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'controllers');
        $array_sys_model = [];
        foreach ($array as $folder => $value) {
            $folder = rtrim($folder, DIRECTORY_SEPARATOR);
            if (is_array($value) === true) {
                foreach ($value as $path) {
                    $array_sys_model[] = strtolower($module . '/' . $folder . '/' . rtrim($path, ".php"));
                }
            } else {
                $array_sys_model[] = strtolower(rtrim($module . '/' . $value, ".php"));
            }
        }
        return $array_sys_model;
    }

    private function whitelistSys()
    {
        $array = directory_map(APPPATH . 'controllers');
        $array_sys_model = [UploadModel::$rootFolder];
        foreach ($array as $folder => $value) {
            $folder = rtrim($folder, DIRECTORY_SEPARATOR);
            if (is_array($value)) {
                foreach ($value as $path) {
                    if (is_array($path)) {
                        continue;
                    }
                    $array_sys_model[] = strtolower($folder . '/' . rtrim($path, ".php"));
                }
            } else {
                $array_sys_model[] = strtolower(rtrim($value, ".php"));
            }
        }
        return $array_sys_model;
    }

    private function routes()
    {
        $arrayRoutes = [];
        $array = array_keys(CIRouter()->routes);
        if (empty($array) === false) {
            foreach ($array as $value) {
                if ($value === '404_override') {
                    continue;
                }
                $arrayRoutes[] = $value;
            }
        }

        return $arrayRoutes;
    }

    /**
     * @OA\Post(
     *     path="/api/whitelist/index",
     *       tags={"core"},
     *          
     *     @OA\Response(response="200", description="An example resource")
     * )
     */
    public function index()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $listdb[] = $this->routes();
        $listdb[] = $this->whitelistSys();
        $array_sub = directory_map(FCPATH . 'modules');
        if (empty($array_sub) === false) {
            $modules = array_keys($array_sub);
            foreach ($modules as $module) {
                $arr = $this->whitelistModule($module);
                if (empty($arr) === false) {
                    $listdb[] = $arr;
                }
            }
        }
        $whitePaths = array_reduce($listdb, 'array_merge', []);
        ApiModel::out($whitePaths, 100, $apiLogId);
    }
}
