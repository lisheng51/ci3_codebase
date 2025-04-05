<?php

class SendMailFileModel
{
    use BasicModel;
    public static function __constructStatic()
    {
        
        self::$table = "send_mail_file";
        self::$primaryKey = "mail_file_id";
        self::$selectOrderBy = [
            'mail_file_id#desc' => 'ID (aflopend)',
            'file_name#desc' => 'Naam (aflopend)',
            'file_name#asc' => 'Naam (oplopend)',
            'created_at#desc' => 'Tijd (aflopend)',
            'created_at#asc' => 'Tijd (oplopend)',
        ];
    }

    public static function getExist(array $input = []): array
    {
        $data['file_name'] = $input['file_name'];
        $data[SendMailModel::$primaryKey] = $input[SendMailModel::$primaryKey];
        self::$sqlWhere = $data;
        return self::getOne();
    }

    public static function upload(int $mail_id = 0, array $files = [])
    {
        if ($mail_id > 0 && !empty($files)) {
            foreach ($files as $rsdb) {
                $filename = $rsdb['file_name'];
                $data['file_name'] = $filename;
                $data[SendMailModel::$primaryKey] = $mail_id;
                $rsdbFile = self::getExist($data);
                if (empty($rsdbFile)) {
                    $base64String = $rsdb['base64'];
                    $data['base64'] = $base64String;
                    self::add($data);
                }
            }
        }
    }
}
