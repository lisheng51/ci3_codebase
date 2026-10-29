<?php

class LanguageTagModel
{

    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "language_tag";
        self::$primaryKey = "language_tag_id";
        self::$selectOrderBy = [
            'language_tag_id#desc' => 'ID (aflopend)',
            LanguageModel::$table . '.folder#asc' => 'Taal (oplopend)',
            LanguageModel::$table . '.folder#desc' => 'Taal (aflopend)',
            'tag#desc' => 'Tag (aflopend)',
            'tag#asc' => 'Tag (oplopend)'
        ];
    }

    public static function updateCache(string $lang = ''): array
    {
        $jsonFile =  $lang . '_' . self::$table . '.json';
        $listdb = self::AllByLanguage($lang);
        $response = json_encode($listdb);
        update_cache_file($jsonFile, $response);
        self::$cacheData[$lang] = $listdb;
        return $listdb;
    }

    // lang() wordt tientallen keren per pagina aangeroepen: bestand maar 1x per taal lezen/decoden
    private static array $cacheData = [];

    public static function fetchCache(string $lang = ""): array
    {
        if (isset(self::$cacheData[$lang])) {
            return self::$cacheData[$lang];
        }
        $jsonFile =  $lang . '_' . self::$table . '.json';
        $data = json_decode(get_cache_file($jsonFile), true);
        if (is_array($data)) {
            return self::$cacheData[$lang] = $data;
        }
        return self::updateCache($lang);
    }

    public static function fetch(string $tag = '', string $setLang = ""): string
    {
        $lang = LanguageModel::getLanguage();
        if (!empty($setLang)) {
            $lang = $setLang;
        }
        $listdb = self::fetchCache($lang);
        return $listdb[$tag] ?? "";
    }

    public static function AllByLanguage(string $lang = ""): array
    {
        self::joinLanguage();
        $where[LanguageModel::$table . '.folder'] = $lang;
        self::$sqlWhere = $where;
        $listdb = self::getAll();
        if (empty($listdb)) {
            return [];
        }
        return array_column($listdb, 'value', 'tag');
    }

    public static function getExist(array $data = [], int $editId = 0, string $tableField = 'tag', string $dataField = 'tag'): array
    {
        $where[self::$table . '.' . $tableField] = $data[$dataField];
        $where[self::$table . '.' . LanguageModel::$primaryKey] = $data[LanguageModel::$primaryKey];
        $where[self::$table . '.' . self::$primaryKey . '!='] = $editId;
        self::$sqlWhere = $where;
        return self::getOne();
    }

    public static function getPostdata(): array
    {
        $data[LanguageModel::$primaryKey] = CIInput()->post(LanguageModel::$primaryKey);
        $data["tag"] = CIInput()->post("tag");
        $data["value"] = CIInput()->post("value");
        AjaxckModel::value('tag', $data["tag"]);
        AjaxckModel::value('value', $data["value"]);
        return $data;
    }

    public static function joinLanguage()
    {
        self::$sqlSelect =
            [
                [LanguageModel::$table => '*'],
                [self::$table => '*']
            ];

        self::$sqlJoin =
            [
                [LanguageModel::$table => LanguageModel::$table . '.' . LanguageModel::$primaryKey . ' = ' . self::$table . '.' . LanguageModel::$primaryKey],
            ];
    }
}
