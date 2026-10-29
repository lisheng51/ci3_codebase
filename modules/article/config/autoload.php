<?php
$path = explode(DIRECTORY_SEPARATOR, dirname(__FILE__, 2));
includeModuleModel(end($path));
$autoload['helper'] = ['load'];
