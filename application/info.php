<?php

$data["path"] = ModuleModel::CorePath;
$data["use_path"] = false;
$data["path_description"] = '_Core';
$data["changelog"] = [
    [
        "name" => "1.0",
        "created_at" => "2018-01-15 15:13:54",
        "info" => [
            "API module",
            "Config module",
            "Taal module",
            "Module module",
            "Rechten module",
            "Bookmark module",
            "History module",
            "Bestand module",
            "App log module",
            "Account module",
            "Spamword module",
            "Bericht module",
            "Visitor module",
            "Mail module",
            "Error log module",
            "Mail config module",
            "Mail template module"
        ]
    ],
    ["name" => "1.1", "created_at" => "2020-10-10 09:13:00", "info" => ["static models"]],
    ["name" => "1.2", "created_at" => "2021-10-10 09:13:00", "info" => ["modules folder buiten application"]],
    ["name" => "1.3", "created_at" => "2024-11-12 09:13:00", "info" => ['trait DbCrud']],
    ["name" => "1.4", "created_at" => "2025-04-16 11:00:00", "info" => ['webapp_=>_core_']]
];
