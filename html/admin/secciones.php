<?php
$template = "secciones.html";
/*
if( isset($_POST['ajax']) ){
    if( isset($_POST['action']) ){
        $action = $_POST['action'];
        switch($action){
        case 'add':
            $result = true;
            if( isset($_POST['padre']) && isset($_POST['name']) ){
                $padre = $_POST['padre'];
                $name  = $_POST['name']; 
                $cur = $dbh->query("SELECT * FROM section WHERE id=?;", array($padre));
                if( !empty($cur) ){
                    $cur = $dbh->query("INSERT INTO section(name, parentSection, sectionOrder, active) VALUES(?,?,?,?);", array($name, $padre, 0.1, 1));
                    if( $cur === false ){
                        $result = false;
                    }
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
            if( $result ){
                $view->set("success",true);
            } else {
                $view->set("error", true);
            }
        }
    }
}
//*/

$tmp = readSections();
$secciones = array();
foreach( $tmp as $i=>$row ){
    if($row['id']==-1){
        $secciones[-1] = $row;
        unset($tmp[$i]);
    }
}
$secciones[-1]['childs'] = $tmp;
$view->set("tree",$secciones);
$cur = $dbh->query("select * from section;");
$view->set("sections", $cur);
function readSectionsAdmin($start=null, $limit = 1){
    global $dbh;
    $sections = array();
    if( $limit<3 ){
        if( $start==null ){
            $cur = $dbh->query("SELECT * FROM section WHERE id=?;", array(-1));
            if( !empty($cur) ){
                $cur[0]['childs'] = readSectionsAdmin(-1, 1);
                $sections[] = $cur[0];
            }
        } else {
            $cur = $dbh->query("SELECT * FROM section WHERE parentSection=? order by sectionOrder;", array($start));
            if( !empty($cur) ){
                foreach( $cur as $row ){
                    if( $row['id']!=-1 ){
                        $childs = readSectionsAdmin($row['id'], $limit+1);
                        if( !empty($childs) ){
                            $row['childs'] = $childs;
                        }
                        $sections[] = $row;
                    }
                }
            }
        }
    }
    return $sections;
}
?>
