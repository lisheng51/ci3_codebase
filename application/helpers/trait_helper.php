<?php
$traitFolder = 'traits';
$files = glob(__DIR__ . DIRECTORY_SEPARATOR . $traitFolder . DIRECTORY_SEPARATOR . '*.php');
foreach ($files as $filename) {
    include_once $filename;
}

function CI(): CI_Controller
{
    $CI = &get_instance();
    return $CI;
}

function CIUri(): CI_URI
{
    $object = &load_class('URI', 'core');
    return $object;
    //return CI()->uri;
}

function CIAgent(): CI_User_agent
{
    $object = &load_class('User_agent');
    return $object;
    //return CI()->agent;
}

function CIDb(): CI_DB_mysqli_driver
{
    return CI()->db;
}

function CIDbUtility(): CI_DB_utility
{
    CI()->load->dbutil();
    return CI()->dbutil;
}

function CISession(): CI_Session
{
    return CI()->session;
}

function CIBenchmark(): CI_Benchmark
{
    $object = &load_class('Benchmark', 'core');
    return $object;
    //return CI()->benchmark;
}

function CIInput(): CI_Input
{
    $object = &load_class('Input', 'core');
    return $object;  // CI()->input;
}

function CIOutput(): CI_Output
{
    $object = &load_class('Output', 'core');
    return $object;  // CI()->output;
}

function CILoader(): CI_Loader
{
    $object = &load_class('Loader', 'core');
    return $object;  // CI()->load;
}

function CIRouter(): MX_Router
{
    $object = &load_class('Router', 'core');
    return $object;  // CI()->router;
}

function CISecurity(): CI_Security
{
    $object = &load_class('Security', 'core');
    return $object;  // CI()->security;
}

function CILang(): CI_Lang
{
    $object = &load_class('Lang', 'core');
    return $object; //CI()->lang;
}

function CIPagination(): CI_Pagination
{
    $object = &load_class('Pagination');
    return $object;
    //return CI()->pagination;
}

function CIImageLib(): CI_Image_lib
{
    $object = &load_class('Image_lib');
    return $object;
    //return CI()->image_lib;
}

function CIEncryption(): CI_Encryption
{
    $object = &load_class('Encryption');
    return $object;
    //return CI()->encryption;
}

includeCoreModel();
