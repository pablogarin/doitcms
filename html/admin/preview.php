<?php
include_once("Compiler.class.php");
$view->set("MENU",$view->getView());
$id = $matches[1];
$view->set("sections", readSections());
$c = new \classes\Compiler("home",$id);
print $c->getCompiledView();
?>
