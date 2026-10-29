<?php

$path = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$data["path"] = end($path);
$data["use_path"] = true;
$data["path_description"] = 'Webmaster';
// $data["arrUploadFolder"] = [
//     $data["path"] . DIRECTORY_SEPARATOR . 'message',
//     $data["path"] . DIRECTORY_SEPARATOR . 'link',
// ];
$data["changelog"] = [
    [
        "name" => "1.0.0",
        "created_at" => "2018-01-15 15:13:54",
        "info" => [
            "Article module",
            "Access module",
        ]
    ],
    [
        "name" => "1.1.0",
        "created_at" => "2021-08-01 16:40:54",
        "info" => [
            "Datum en tijd in order wijzigen",
        ]
    ]
];
