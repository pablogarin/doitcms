<?php
error_reporting(E_ALL);

session_start();

$path = $_SERVER['DOCUMENT_ROOT'];

$path = str_replace('/html','',$path);
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$path);

$html = $path."/html";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$html);

$classes = $path."/classes";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$classes);

$models = $path."/models";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$models);

$includes = $path."/includes";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$includes);

$libs = $path."/html/libs";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$libs);


define('PATH',$path);
define('COOKIE',md5($path));

// INCLUDES
include_once 'View.class.php';
include_once 'install.php';
include_once 'connect.php';
include_once 'models.php';
include_once 'classes.php';
include_once 'functions.php';

$view = new View();
$view->setFolder(PATH."/templates");

?>
