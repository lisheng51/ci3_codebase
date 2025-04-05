<?php

class MailTempModel
{

    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "mail_temp";
        self::$primaryKey = "mail_temp_id";
        self::$usingSoftDel = true;
        self::$selectOrderBy = [
            'mail_temp_id#desc' => 'ID (aflopend)',
            LanguageModel::$table . '.folder#asc' => 'Taal (oplopend)',
            LanguageModel::$table . '.folder#desc' => 'Taal (aflopend)',
            'subject#desc' => 'Onderwerp (aflopend)',
            'subject#asc' => 'Onderwerp (oplopend)'
        ];
    }

    public static function getByTrigger(string $trigger = 'contact', string $setLang = ''): array
    {
        $data['trigger_name'] = $trigger;
        $data[LanguageModel::$primaryKey] = LanguageModel::fetchId($setLang);
        $arrData = self::getExist($data);
        if (empty($arrData) || $arrData[self::$fieldIsDel] > 0) {
            $arrData = MailTemplateModel::getByMethod($trigger);
            return $arrData;
        }

        $temp[$trigger . '_subject'] = $arrData['subject'];
        $temp[$trigger . '_body'] = $arrData['body'];
        return $temp;
    }


    public static function getExist(array $data = [], int $editId = 0, string $tableField = 'trigger_name', string $dataField = 'trigger_name'): array
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
        $data["trigger_name"] = CIInput()->post("trigger_name");
        $data["description"] = CIInput()->post("description");
        $data["subject"] = CIInput()->post("subject");
        $data["body"] = CIInput()->post("body");
        AjaxckModel::value('trigger_name', $data["trigger_name"]);
        AjaxckModel::value('subject', $data["subject"]);
        AjaxckModel::value('body', $data["body"]);
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
