<?php
include_once '../common.php';
include_once 'login.php';

if( isset($_REQUEST['p']) && !empty($_REQUEST['p']) ){
    $page = $_REQUEST['p'];
} else {
    $page = "home";
}

switch( $page ){
case 'home':
    include_once("home.php");
    break;
default:
    $compiler = new \classes\Compiler($page,"admin");
    print $compiler->getCompiledView();
    exit;
    break;
}

$view->setFolder(PATH."/templates/admin");
$view->setTemplate($template);
print $view->getView();

?>
