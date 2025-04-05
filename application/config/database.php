<?php

defined('BASEPATH') or exit('No direct script access allowed');

$active_group = 'default';
$query_builder = true;

$db['default'] = [
    'dsn' => '',
    'hostname' => ENVIRONMENT_HOSTNAME,
    'username' => ENVIRONMENT_USERNAME,
    'password' => ENVIRONMENT_PASSWORD,
    'database' => ENVIRONMENT_DATABASE,
    'dbdriver' => 'mysqli',
    'dbprefix' => 'bc_',
    'pconnect' => false,
    'db_debug' => (ENVIRONMENT === 'development'),
    'cache_on' => false,
    'cachedir' => FCPATH . 'cache',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt' => false,
    'compress' => false,
    'stricton' => false,
    'failover' => [],
    'save_queries' => (ENVIRONMENT === 'development')
];
