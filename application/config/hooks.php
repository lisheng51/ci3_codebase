<?php

$hook['pre_system'][] = [
  'class' => 'Pre_system',
  'function' => 'library_autoload',
  'filename' => 'Pre_system.php',
  'filepath' => 'hooks'
];

$hook['pre_system'][] = [
  'class' => 'Pre_system',
  'function' => 'webmaster_vendor_autoload',
  'filename' => 'Pre_system.php',
  'filepath' => 'hooks'
];

// if (ENVIRONMENT === 'development') {
//   $hook['post_controller'][] = [
//     'class' => 'Post_controller',
//     'function' => 'db_query_log',
//     'filename' => 'Post_controller.php',
//     'filepath' => 'hooks'
//   ];
// }
