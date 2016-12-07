<?php
error_reporting(E_ALL);

session_start();

$path = $_SERVER['DOCUMENT_ROOT'];

$path = str_replace('/html','',$path);
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$path);

$html = $path."/html";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$html);

$classes = $path."/classes";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$classes);

$models = $path."/models";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$models);

$includes = $path."/includes";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$includes);

$libs = $path."/html/libs";
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.$libs);


define('PATH',$path);
define('COOKIE',md5($path));

// INCLUDES
include_once 'View.class.php';
include_once 'install.php';
include_once 'connect.php';
include_once 'models.php';
include_once 'classes.php';
include_once 'functions.php';

$view = new View();
$view->setFolder(PATH."/templates");

$cur = $dbh->query("SELECT * FROM config;");
$configs = array();
foreach( $cur as $row ){
    if( $row['name']=="layout" ){
        define("LAYOUT", $row['value']);
        $view->set("layoutId", LAYOUT);
    }
    if( $row['name']=="Nombre del Sitio" ){
        define("TITLE", $row['value']);
        $view->set("title", $row['value']);
    }
    $configs[$row['id']] = $row['value'];
}
$sections = readSections();
$view->setFolder(PATH."/templates/");
$view->setTemplate("menu.phtml");
$view->set("sections", $sections);
function readSections($start=null, $limit = 1){
    global $dbh;
    $sections = array();
    if( $limit<3 ){
        if( $start==null ){
            $cur = $dbh->query("SELECT * FROM section WHERE id=?;", array(-1));
            if( !empty($cur) ){
                //$cur[0]['childs'] = readSections(-1, 1);
                $cont = $dbh->query("SELECT * FROM content WHERE tableName='section' AND keyValue=?;",array($cur[0]['id']));
                if( !empty($cont) ){
                    $cur[0]['url'] = $cont[0]['url'];
                }
                $sections[] = $cur[0];
                $sections = array_merge($sections,readSections(-1, 1));
            }
        } else {
            $cur = $dbh->query("SELECT * FROM section WHERE parentSection=? order by sectionOrder;", array($start));
            if( !empty($cur) ){
                foreach( $cur as $row ){
                    if( $row['id']!=-1 ){
                        $cont = $dbh->query("SELECT * FROM content WHERE tableName='section' AND keyValue=?;",array($row['id']));
                        if( !empty($cont) ){
                            $row['url'] = $cont[0]['url'];
                        }
                        /*
                        $temp = $dbh->query("SELECT * FROM template WHERE name=?;",array(str_replace("/","",$row['url'])));
                        if( !empty($temp) ){
                            $row['description'] = $temp[0]['description'];
                        }
                        //*/
                        $childs = readSections($row['id'], $limit+1);
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
