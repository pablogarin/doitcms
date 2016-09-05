<?php
namespace classes;
include_once 'TableFactory.class.php';
class Compiler {
    private $url;
    function __construct($url = "home"){
        $this->url = $url;
    }
    function getDomsRecursive( $id = -1, $doms=array() ){
        global $dbh;
        $cur = $dbh->query("SELECT * FROM dom WHERE id=? order by domOrder;",array($id));
        if( empty($cur) ){
            return false;
        }
        foreach($cur as $dom){
            $subCur = $dbh->query("SELECT * FROM atribute WHERE id IN (SELECT idAtribute FROM dom_atribute WHERE idDom=?);", array($dom['id']));
            if( !empty($subCur) ){
                foreach($subCur as $atr){
                    if( !isset($dom['atributes']) ){
                        $dom['atributes'] = array();
                    }
                    $dom['atributes'][] = $atr;
                }
            }
            $doms[$dom['id']] = $dom;
            $subCur = $dbh->query("SELECT * FROM dom WHERE id!=? AND parentDom=?;",array($id, $id));
            if( !empty($subCur) ){
                $doms[$dom['id']]['hijos'] = array();
                foreach( $subCur as $child ){
                    $doms[$dom['id']]['hijos'][$child['id']] = $this->getDomsRecursive($child['id']);
                }
            }
        }
        return $doms;
    }
    function parseRecursive($domTree, $tab =""){
        global $dbh;
        $tab .= "\t";
        foreach($domTree as $id=>$dom){
            $tag = "";
            if($dom['id']!='-1')
                $tag .= "$tab";
            $tag .= "<".$dom['type'];
            if( !empty($dom['domId']) ){
                $tag.=" id=\"".$dom['domId']."\"";
            }
            if( !empty($dom['className']) ){
                $tag.=" class=\"".$dom['className']."\"";
            }
            if( isset($dom['atributes']) ){
                foreach( $dom['atributes'] as $atr ){
                    $tag .= " ".$atr['name']."=\"".$atr['value']."\"";
                }
            }
            if( $dom['closeTag'] ){
                if($dom['id']!='-1')
                    $tag .= ">\n{{CHILD}}$tab</".$dom['type'].">";
                else
                    $tag .= ">\n{{CHILD}}</".$dom['type'].">";
                $content = "";
                if( isset($dom['hijos']) && !empty($dom['hijos']) ){
                    foreach( $dom['hijos'] as $child ){
                        $content .= $this->parseRecursive($child, $tab);
                    }
                    $tag = str_replace("{{CHILD}}",$content, $tag);
                } else {
                    //TODO: place this on a factory class
                    $contCur = $dbh->query("SELECT * FROM content WHERE id IN (SELECT idContent FROM content_dom WHERE idDom=?);",array($dom['id']));
                    if( !empty($contCur) ){
                        $table = $contCur[0]['tableName'];
                        $keyName = $contCur[0]['keyName'];
                        $keyValue = $contCur[0]['keyValue'];
                        $content = $dbh->query("SELECT * FROM $table WHERE $keyName=?",array($keyValue));
                        switch($table){
                        case 'text':
                            $dom['content'] = array($content[0]['body']);
                            break;
                        case 'section':
                            $dom['content'] = array($content[0]['name']);
                            break;
                        }
                    }
                    if( isset($dom['content']) && !empty($dom['content']) ){
                        foreach( $dom['content'] as $child ){
                            $tag = str_replace("\n{{CHILD}}$tab","$child", $tag);
                        }
                    } else {
                        $tag = str_replace("\n{{CHILD}}$tab","", $tag);
                    }
                }
            } else {
                $tag .= "/>";
            }
        }
        $tag .="\n";
        return $tag;
    }
    public function getCompiledView(){
        //TODO: check if si cached, else create it and save it to file...
        return "<!DOCTYPE html>\n".$this->parseRecursive($this->getDomsRecursive());
    }
}

?>
