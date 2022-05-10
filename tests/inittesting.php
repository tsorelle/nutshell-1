<?php
$projectFileRoot = '/dev/twoquakers/nutshell-1/web.root/';
error_reporting(E_ALL & ~E_NOTICE);
session_set_cookie_params(604800);
session_start();
define('DIRNAME_CORE', 'nutshell');
define('DIR_BASE',$projectFileRoot);
define('DIR_APPLICATION', DIR_BASE . '/application');
define('DIR_CONFIG_SITE', DIR_APPLICATION . '/config');
include_once $projectFileRoot.'nutshell\src\tops\sys\TPath.php';
\Tops\sys\TPath::Initialize($projectFileRoot,'/application/config');
include_once DIR_CONFIG_SITE . "/peanut-bootstrap.php";
\Peanut\Bootstrap::initialize($projectFileRoot);
