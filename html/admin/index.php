<?php
include_once '../common.php';
include_once 'login.php';

if( isset($_REQUEST['p']) && !empty($_REQUEST['p']) ){
    $page = $_REQUEST['p'];
} else {
    $page = "dashboard-admin";
}

switch( $page ){
case 'dashboard-admin':
    include_once("dashboard-admin.php");
    break;
default:
    $compiler = new \classes\Compiler($page,"admin");
    print $compiler->getCompiledView();
    exit;
    break;
}

$view->setFolder(PATH."/templates/admin");
$view->setTemplate($template);
$content = $view->getView();
$view->set("CONTENT", $content);
$view->setTemplate("layout.html");
print $view->getView();

?>
