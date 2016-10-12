<?php
require_once 'common.php';

$page = "home";
if( isset($_REQUEST['p']) ){
    $page = $_REQUEST['p'];
}
if( empty($page) )
    $page = "home";

$compiler = new \classes\Compiler($page);
print $compiler->getCompiledView();
?>
