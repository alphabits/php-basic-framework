<?php

// MISC FUNCTIONS 

function get_config($k) {
    global $C;
    if (!array_key_exists($k, $C)) {
        throw new Exception(sprintf('Config key (%s) not found', $k));
    }

    return $C[$k];
}

function is_debug() {
    return get_config('debug');
}


// SESSION

function init_session($lifetime, $cookie_name) {
    session_start();
}


// DB

function db_connect($dbhost, $user, $pass, $dbname) {
    $conn_string = sprintf('mysql:dbname=%s;host=%s', $dbname, $dbhost);
    return new PDO($conn_string, $user, $pass);
}


// ROUTING

$_internal_url_map = array();
function _init_router($url_map) {
    global $_internal_url_map;

    foreach ($url_map as $rule) {
        list($pattern, $endpoint) = $rule;
        $tmp = _parse_url_pattern($pattern);
        $tmp['endpoint'] = $endpoint;
        $_internal_url_map[$endpoint] = $tmp;
    }
}

function _parse_url_pattern($pattern) {
    $url_field_types = get_config('url_field_types');

    $varnames = array();
    $reg_pattern = preg_replace_callback('(<([a-z]+):([a-z]+)>)', function ($matches) use ($url_field_types, &$varnames) {
        $varnames[] = $matches[2];
        return sprintf('(?P<%s>%s)', $matches[2], $url_field_types[$matches[1]]);
    }, $pattern);
    $reg_pattern = '#^'.$reg_pattern.'$#';
    
    return compact('reg_pattern', 'pattern', 'varnames');
}

function route_request($path) {
    global $_internal_url_map;

    foreach ($_internal_url_map as $endpoint => $setup) {
        if (preg_match($setup['reg_pattern'], $path, $matches)) {
            $vars = array_intersect_key($matches, array_flip($setup['varnames']));
            return compact('endpoint', 'vars');
        }
    }

    return false;
}

function url_for($endpoint, $vars, $absolute=false) {
    global $_internal_url_map;

    if (!array_key_exists($endpoint, $_internal_url_map)) {
        throw new Exception(sprintf("Endpoint %s not found in url_for", $endpoint));
    }

    $setup = $_internal_url_map[$endpoint];
    $pattern = $setup['pattern'];

    foreach ($setup['varnames'] as $var) {
        if (!array_key_exists($var, $vars)) {
            throw new Exception(sprintf('Required var %s missing in url_for', $var));
        }
        $var_pattern = sprintf('#<[a-z]+:%s>#', $var);
        $pattern = preg_replace($var_pattern, $vars[$var], $pattern);
    }

    if ($absolute) {
        $pattern = get_config('url_root') . $pattern;
    }

    return $pattern;
}


// HTTP HELPER FUNCTIONS

function http_set_status($code) {
    static $http_error_codes = array(
        404 => 'Not Found',
        500 => 'Internal Server Error'
    );
    if (!array_key_exists($code, $http_error_codes)) {
        $code = 500;
    }
    header(sprintf('HTTP/1.0 %s %s', $code, $http_error_codes[$code]));
}

function http_error($code) {
    http_set_status($code);
    return render_template($code.'.html');
}

function http_404() {
    return http_error(404);
}

function http_500() {
    return http_error(500);
}

function redirect($url) {
    if (strpos($url, 'http') !== 0) {
        $url = get_config('url_root') . $url;
    }
    header('Location: '.$url);
}


// TEMPLATING

function _render_template($template_name, $ctx=array()) {
    $template_path = get_template_path($template_name);
    extract($ctx);
    ob_start();
    include $template_path;
    return ob_get_clean();
}

function render_template($template_name, $ctx=array(), $master='layout.html') {
    $ctx['__content__'] = _render_template($template_name, $ctx);
    return _render_template($master, $ctx);
}

function get_template_path($template_name) {
    return ROOT . '/templates/' . $template_name;
}

function url($endpoint, $vars=array()) {
    echo url_for($endpoint, $vars, $abs=true);
}

function static_url($type, $filename) {
    url('static', compact('type', 'filename'));
}

function link_to($text, $endpoint, $vars=array()) {
    echo sprintf('<a href="%s">%s</a>', url_for($endpoint, $vars, $abs=true), $text);
}


// APP

function run_app($c) {
    _init_router($c['url_map']);

    $path = isset($_SERVER['PATH_INFO']) ? rtrim($_SERVER['PATH_INFO'], '/') : '' ;
    $route = route_request($path);
    
    if ($route === false) {
        return http_404();
    }

    $controller = 'run_' . $route['endpoint'];

    if (!function_exists($controller)) {
        return http_500();
    }

    echo 'Running ' . $controller;

    return $controller($route['vars']);
}
