<?php

define('WEBROOT', dirname(__FILE__));
define('ROOT', WEBROOT.'/..');

// Change this path so it points on your config file
define('CONFIG_PATH', ROOT.'/my_config.php');


include ROOT.'/functions.php';
include ROOT.'/controllers.php';
include ROOT.'/models.php';

$C = include CONFIG_PATH;

$C['db'] = db_connect($C['db_host'], $C['db_user'], $C['db_pass'], $C['db_name']);
init_session($C['session_lifetime'], $C['session_cookie_name']);

echo run_app($C);

var_dump($_internal_url_map);
