<?php

return array(
    
    'debug' => true,

    'db_host' => '127.0.0.1',
    'db_name' => 'test',
    'db_user' => 'username',
    'db_pass' => 'abc',

    'session_lifetime' => 20*60,
    'session_cookie_name' => 'kukkie',

    'url_root' => 'http://localhost/eksperten/php-framework/web/index.php',

    'url_field_types' => array(
        'slug' => '[-a-z_]+',
        'int' => '[0-9]+',
        'static' => '[js|css|img]',
        'file' => '[-a-z_]\.[a-z]{1,5}'
    ),
    'url_map' => array(
        array('/', 'index'),
        array('/blog', 'blogindex'),
        array('/blog/<int:id>-<slug:slug>', 'blogpost'),
        array('/<slug:pagename>', 'page'),
        array('/static/<static:type>/<file:filename>', 'static')
    )
);
