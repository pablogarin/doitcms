<?php
namespace classes;
class Parser {
    private $htmlString;
    private $doc;
    private $closedTags = array("a", "img", "meta", "link");

    function __construct($htmlString)
    {
        global $dbh;
        $this->htmlFile = $htmlString;
        $this->doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $this->doc->loadHTML($htmlString);
        $this->body = $this->findBody();
        libxml_clear_errors();
    }

    private function findBody($dom=null){
        if( $dom==null ){
            $dom = $this->doc;
        }
        $body = $dom;
        if( get_class($dom)=='DOMDocument' ){
            if( $dom->nodeName!='body' ){
                $body = $this->findBody($dom->childNodes);
            }
        } else if( get_class($dom)=='DOMNodeList' ){
            foreach( $dom as $child ){
                if( get_class($child)=="DOMElement" ){
                    if( $child->nodeName!='body' ){
                        $body = $this->findBody($child->childNodes);
                    } else {
                        $body = $child;
                    }
                }
            }
        }
        return $body;
    }

    public function toJSON(){
        return json_encode($this->readRecursive());
    }

    private $recursion = -1;

    function readRecursive($node=null)
    {
        //*
        if( $node == null ){
            $node = $this->body->childNodes;
        }
        $retval = array();
        $this->recursion++;
        foreach( $node as $item ){
            if(get_class($item)=="DOMElement"){
                if( $this->recursion-1==0 ){
                    $this->recursion += 1;
                }
                $domElement = array(
                    "type" => $item->nodeName, 
                    "domId" => null,
                    "className" => null,
                    "closeTag"=>(int)(!in_array($item->nodeName, $this->closedTags)),
                    "parentDom" => -1,
                    "domOrder" => 1.0
                );
                $atributes = array();
                foreach( $item->attributes as $atr ){
                    $atributes[$atr->name] = $atr->value;
                }
                if( !empty($atributes) ){
                    foreach( $atributes as $name=>$value ){
                        $atr = array("name"=>$name, "value"=>$value);
                        if( $name=="id" ){
                            $domElement["domId"] = $value;
                            unset($atributes[$name]);
                            $atr = array();
                        }
                        if( $name=="class" ){
                            $domElement["className"] = $value;
                            unset($atributes[$name]);
                            $atr = array();
                        }
                        if( !empty($atr) )
                            $domElement['atributes'][] = $atr;
                    }
                    /*
                    if( count($atributes)>0 ){
                        $domElement['atributes'] = $atributes;
                    }
                    //*/
                }
                $cd = $item->childNodes;
                $hijos =  $this->readRecursive($cd);
                if( empty($hijos) ){
                    if( !empty($item->nodeValue) ){
                        $domElement["nodeValue"] = $item->nodeValue;
                    }
                } else {
                    $domElement["hijos"] = $hijos;
                }
                $retval[] = $domElement;
            }
        }
        return $retval;
    }
    function saveTemplate( $name=false ){
        global $dbh;
        if( $name===false or gettype($name)!=='string' ){
            throw new Exception("No puede dejar el nombre del template en blanco.");
            exit;
        }
        if( $name!=null ){
            $this->templateName = $name;
            $cursor = $dbh->query("SELECT * FROM template WHERE name=?;",array($name));
            if( !empty($cursor) ){
                $this->idTemplate = $cursor[0]['id'];
                $cur = $dbh->query("SELECT idDom FROM template_dom WHERE idTemplate=?;",array($this->idTemplate));
                $doms = array();
                foreach($cur as $idDom){
                    $doms[] = $idDom['idDom'];
                }
                $idDoms = join(",",$doms);
                $cursor = $dbh->query("DELETE FROM text WHERE id IN (SELECT keyValue FROM content WHERE id IN (SELECT id FROM content_dom WHERE idDom IN (".$idDoms.")))");
                $cursor = $dbh->query("DELETE FROM content WHERE id IN (SELECT id FROM content_dom WHERE idDom IN (".$idDoms."))");
                $cursor = $dbh->query("DELETE FROM content_dom WHERE idDom IN (".$idDoms.")");
                $cursor = $dbh->query("DELETE FROM atribute WHERE id IN (SELECT idAtribute FROM dom_atribute WHERE idDom IN (".$idDoms.")");
                $cursor = $dbh->query("DELETE FROM dom_atribute WHERE idDom IN (".$idDoms.");");
                $cursor = $dbh->query("DELETE FROM template_dom WHERE idDom IN (".$idDoms.");");
                $cursor = $dbh->query("DELETE FROM dom WHERE id IN (".$idDoms.");");
            } else {
                $this->idTemplate = $dbh->query("INSERT INTO template(name) VALUES('".$name."')");
            }
        }
        if( !$this->idTemplate ){
            throw new Exception("No se pudo grabar el template.");
            exit;
        }
        return $this->saveRecursive();
    }
    function saveRecursive($dom=null, $parent=10){
        global $dbh;
        $retval = true;
        $order = 0.1;
        if( $dom==null ){
            $dom = $this->readRecursive()[0];
        }
        $data = array("type" => $dom["type"], "domId" => $dom["domId"], "className" => $dom["className"],"closeTag" => $dom["closeTag"], "parentDom" => $parent, "domOrder" => $order );
        $insert = array_values($data);
        $idDom = $dbh->query($q = "INSERT INTO dom(type, domId, className, closeTag, parentDom, domOrder) VALUES('".join("','",$insert)."');");
        $cur = $dbh->query($q = "INSERT INTO template_dom(idTemplate, idDom) VALUES(?,?);",array($this->idTemplate, $idDom));
        if( $cur===false ){
            return false;
        }
        if( isset($dom["atributes"]) ){
            $atrs = $dom["atributes"];
            foreach( $atrs as $atr ){
                $idAtribute = $dbh->query($q = "INSERT INTO atribute(name,value) VALUES('".join("','",$atr)."');");
                $ins = $dbh->query( $q = "INSERT INTO dom_atribute(idDom, idAtribute) VALUES(".$idDom.", ".$idAtribute.");");
            }
        }
        if( isset($dom['nodeValue']) ){
            $name       = $dom['type']."-TEXT-".$idDom;
            $url        = $this->templateName;
            $tableName  = "text";
            $keyName    = "id";
            $body       = $dom['nodeValue'];
            $keyValue   = $dbh->query("INSERT INTO text(name,body) VALUES(?,?);",array($name, $body));
            $idContent  = $dbh->query("INSERT INTO content(name,url,tableName,keyName,keyValue) VALUES(?,?,?,?,?);", array($name, $url, $tableName, $keyName, $keyValue));
            $res        = $dbh->query("INSERT INTO content_dom(idContent,idDom) VALUES(?,?);",array($idContent, $idDom));
            if( is_numeric($res) && $res!=0 ){
                $res = true;
            } else {
                $res = false;
            }
            $retval &= $res;
        }
        if( isset($dom['hijos']) and is_array($dom['hijos']) and !empty($dom['hijos']) ){
            foreach( $dom["hijos"] as $hijo ){
                $res = $this->saveRecursive($hijo, $idDom);
                if( is_numeric($res) && $res!=0 ){
                    $res = true;
                } else {
                    $res = false;
                }
            }
        }
        $order+=0.1;
        return $retval;
    }

    public function getIdTemplate(){
        return $this->idTemplate;
    }
}
?>
