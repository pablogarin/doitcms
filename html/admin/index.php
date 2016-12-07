<?php
include_once '../common.php';
include_once 'login.php';

if( isset($_REQUEST['p']) && !empty($_REQUEST['p']) ){
    $page = $_REQUEST['p'];
} else {
    $page = "configuraciones";
}
include_once("navs.php");

switch( $page ){
case 'service':
    include_once("service.php");
    exit;
case 'secciones':
    include_once("secciones.php");
    break;
case 'configuraciones':
    include_once("configuraciones.php");
    break;
case 'configuraciones':
    include_once("configuraciones.php");
    break;
case preg_match('/preview\/([-0-9]+)\//', $page, $matches)>0:
    include_once("preview.php");
    exit;
case preg_match('/edit\/([0-9]+)\//', $page, $matches)>0:
    $id = $matches[1];
    if( $id==1 ){
        include_once("compileStyles.php");
        $template = "edit-layout.html";
    } else {
        include_once("edit.php");
        exit();
    }
    break;
default:
    $template = "error.html";
    break;
    /*
default:
    $compiler = new \classes\Compiler($page,LAYOUT);
    print $compiler->getCompiledView();
    exit;
    break;
     */
}

$view->setFolder(PATH."/templates/admin");
$view->setTemplate($template);
$content = $view->getView();
$view->set("CONTENT", $content);
$view->setTemplate("layout.html");
print $view->getView();

?>
