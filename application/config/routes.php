<?php

$route['default_controller'] = ENVIRONMENT_DEFAULT_CONTROLLER;
$route['404_override'] = '';
$route['translate_uri_dashes'] = false;
$route = array_merge($route, ENVIRONMENT_ROUTES);
