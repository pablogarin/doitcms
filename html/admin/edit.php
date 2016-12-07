<?php
error_reporting(~E_ALL);
include_once("Compiler.class.php");
include_once("Parser.class.php");

if( !empty($id) ){
    if( isset($_POST['name']) && isset($_POST['html']) ){
        /*
         *  LLAMADA AJAX PARA GRABAR PAGINA HTML
         * */
        $retval = array();
        if( !empty($_POST['name']) && !empty($_POST['html']) ){
            $strHTML = htmlentities($_POST['html']);
            $name = $_POST['name'];
            $p = new classes\Parser($strHTML);
            $doms = $p->readRecursive();
            $saved = $p->saveTemplate($name);
            $retval['name'] = $name;
            $retval['ok'] = $saved;
        } else {
            $retval['ok'] = false;
            $retval['exception']['level'] = 'FATAL';
            $retval['exception']['message'] = 'Debe enviar la URL y el contenido en HTML.';
            $retval['exception']['name'] = @$_POST['name'];
            $retval['exception']['html'] = @$_POST['html'];
        }
        header("Content-type: application/json; charset=utf-8");
        print json_encode($retval);
        exit;
    }
    $cur = $dbh->query("SELECT * FROM template WHERE id=?;", array($id));
    if( !empty($cur[0]) ){
        $view->set("url", $cur[0]['name']);
        $view->set("id", $cur[0]['id']);
        $c = new \classes\Compiler($cur[0]['name'], LAYOUT);
        $jsonData = json_encode($c->getDomsRecursive());
        $strHTML = $c->getCompiledView();
        $dom = new DOMDocument();
        $dom->formatOutput = true;
        $dom->loadHTML($strHTML);
        $result = $dom->saveHTML($dom->getElementById('main-content'));
        $result = str_replace("<div id=\"main-content\" class=\"container-fluid\">","", $result);
        $result = substr($result,0,-6);
        $view->set("CONTENT", $result);
    }
}
function findEditableContent($childNodes){
    print_r($childNodes);
    foreach( $childNodes as $node ){
        print_r($node);
    }
    exit;
}
$view->setFolder(PATH."/templates/admin");
$view->setTemplate("edit.html");
print $view->getView();
