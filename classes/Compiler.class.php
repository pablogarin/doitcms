<?php
namespace classes;
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
    function parseRecursive($domTree){
        foreach($domTree as $id=>$dom){
            $tag = "\n<".$dom['type'];
            if( isset($dom['atributes']) ){
                foreach( $dom['atributes'] as $atr ){
                    $tag .= " ".$atr['name']."=\"".$atr['value']."\"";
                }
            }
            if( $dom['closeTag'] ){
                $tag .= ">{{CHILD}}</".$dom['type'].">";
                $content = "";
                if( isset($dom['hijos']) && !empty($dom['hijos']) ){
                    foreach( $dom['hijos'] as $child ){
                        $content .= $this->parseRecursive($child);
                    }
                    $tag = str_replace("{{CHILD}}",$content, $tag);
                } else {
                    // TODO: Insert content
                    if( $dom['type']=="title" ){
                        $dom['content'][] = "Titulo";
                    }
                    if( $dom['type']=="h1" ){
                        $dom['content'][] = "Hola Mundo!";
                    }
                    if( isset($dom['content']) && !empty($dom['content']) ){
                        foreach( $dom['content'] as $child ){
                            $tag = str_replace("{{CHILD}}",$child, $tag);
                        }
                    } else {
                        $tag = str_replace("{{CHILD}}","", $tag);
                    }
                }
            } else {
                $tag .= "/>";
            }
        }
        return $tag;
    }
    public function getCompiledView(){
        //TODO: check if si cached, else create it and save it to file...
        return "<!DOCTYPE html>".$this->parseRecursive($this->getDomsRecursive());
    }
}

?>
