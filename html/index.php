<?php
require_once 'common.php';

$page = $_REQUEST['p'];

$compiler = new \classes\Compiler($page);
print $compiler->getCompiledView();
?>
