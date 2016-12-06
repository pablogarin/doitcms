<?php
include_once("Parser.class.php");
$template = "upload.html";
if( isset($_FILES['html']) ){
    $name = $_POST['name'];
    $file = $_FILES['html'];
    if($file['error']==0){
        $fileName = $file['name'];
        $filePath = PATH."/tmp/".$fileName;
        if( move_uploaded_file($file['tmp_name'], $filePath) ){
            $handle = fopen($filePath, "r");
            $strHTML = "";
            while( $bit = fread($handle, 4096) ){
                $strHTML .= $bit;
            }
            fclose($handle);
            $_POST['html'] = $strHTML;
        }
    }
}
if( isset($_POST['name']) || isset($_POST['html']) ){
    /*
     *  LLAMADA AJAX PARA GRABAR PAGINA HTML
     * */
    $retval = array();
    if( !empty($_POST['name']) && !empty($_POST['html']) ){
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
?>
