<?php
require_once 'common.php';

$page = "home";
if( isset($_REQUEST['p']) ){
    $page = $_REQUEST['p'];
}
if( empty($page) )
    $page = "home";

$view->set("page","/".$page);
$view->set("MENU",$view->getView());

$compiler = new \classes\Compiler($page, LAYOUT);
print $compiler->getCompiledView();
?>
