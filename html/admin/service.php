<?php
header("Content-type: application/json; charset=UTF-8");
$retval = array();
if( isset($_POST['action']) ){
    $action = $_POST['action'];
    $retval['action'] = $action;
    switch($action){
    case 'section':
        if( isset($_POST['id']) && isset($_POST['name']) && isset($_POST['parentSection']) && isset($_POST['sectionOrder']) && isset($_POST['url']) ){
            if( !empty($_POST['id']) && !empty($_POST['name']) && !empty($_POST['parentSection']) && !empty($_POST['sectionOrder']) && !empty($_POST['url']) ){
                $id     = $_POST['id'];
                $new    = false;
                if( $id=="add" ){
                    $new = true;
                }
                $name   = $_POST['name'];
                $parent = $_POST['parentSection'];
                $url    = "/".$_POST['url'];
                $order  = $_POST['sectionOrder'];
                if( $new ){
                    $query  = "INSERT INTO section(name, parentSection, sectionOrder, active) VALUES(?,?,?,1);";
                    $data   = array($name, $parent, $order);
                    $ins    = $dbh->query($query, $data);
                    $cur    = $dbh->query("INSERT INTO content(name,url,tableName,keyName,keyValue) VALUES(?,?,'section','id',?)", array("Menu ".$name,$url,$ins));
                } else {
                    $query  = "UPDATE section SET name=?, parentSection=?, sectionOrder=? WHERE id=?;";
                    $data   = array($name, $parent, $order, $id);
                    $cur    = $dbh->query($query, $data);
                }
                $retval["ok"] = $cur!==false;
            } else {
                $retval["error"] = "Debe ingresar todos los datos.";
            }
        }
        break;
    case 'delete':
        if( isset($_POST['id']) && !empty($_POST['id']) ){
            $id = $_POST['id'];
            $cur = $dbh->query("SELECT * FROM content WHERE tableName='section' AND keyValue=?;", array($id));
            if( !empty($cur) ){
                $url = str_replace("/","",$cur[0]['url']);
                $retval['url'] = $url;
                $cur = $dbh->query("DELETE FROM section WHERE id=?;", array($id));
                $cur2 = $dbh->query("DELETE FROM template WHERE name=?;", array($url));
            }
            $retval['ok'] = $cur!==false && isset($cur2) && $cur2!==false;
        }
        break;
    case 'activate':
        if( isset($_POST['id']) && !empty($_POST['id']) ){
            $id = $_POST['id'];
            $cur = $dbh->query("SELECT * FROM section WHERE id=?;",array($id));
            if( !empty($cur) ){
                $active = 0;
                if( $cur[0]['active']==0 ){
                    $active = 1;
                }
                $cur = $dbh->query("UPDATE section set active=? WHERE id=?;", array($active, $id));
            }
            $retval['ok'] = $cur!==false;
        }
        break;
    case 'page':
        if( isset($_POST['name']) && isset($_POST['description']) && isset($_POST['section']) ){
            // TODO: insert into template and section.
            $_POST['name'] = str_replace('/','',$_POST['name']);
            $sel = $dbh->query("SELECT * FROM template WHERE name=?;",array($_POST['name']));
            if( !empty($sel) ){
                $cur = $dbh->query("UPDATE template SET description=? WHERE name=?;", array($_POST['description'], $_POST['name']));
            } else {
                $cur = $dbh->query("INSERT INTO template(description,name) VALUES(?,?);", array($_POST['description'], $_POST['name']));
            }
            $retval['ok'] = $cur!==false;
            if( !$retval['ok'] )
                $retval['errorInfo'] = $dbh->errorInfo();
        }
        break;
    case 'update-layout':
        if( isset($_POST['id']) ){
            $id = $_POST['id'];
            $cur = $dbh->query("UPDATE config SET value=? WHERE name='layout';", array($id));
            $retval['ok'] = $cur!== false;
        }
        break;
    case 'save-theme':
        break;
    }
    print json_encode($retval);
    exit;
}
$retval["error"] = "No se ha ingresado ni una accion";
print json_encode($retval);
?>
