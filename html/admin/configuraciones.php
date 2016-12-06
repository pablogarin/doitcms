<?php
$template = "configuraciones.html";
if( isset($_POST['grabar']) ){
    $result = true;
    foreach( $_POST['conf'] as $id=>$value ){
        if( empty($value) ){
            $result = false;
        }
        $res = $dbh->query("UPDATE config SET value=? WHERE id=?;", array($value,$id));
        $result &= $res!==false;
    }
    if($result){
        $view->set("success", true);
    }else {
        $view->set("error", true);
    }
}
$configs = $dbh->query("SELECT * FROM config;");
foreach( $configs as $id=>$cfg ){
    if( preg_match_all('/select\[([\,a-zA-Z0-9=]+)\]/',$cfg['type'], $matches) ){
        $options = explode(",",$matches[1][0]);
        $configs[$id]['options'] = $options;
        $configs[$id]['type'] = 'select';
    }
}
$view->set("configs",$configs);
?>
