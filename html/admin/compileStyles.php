<?php
require "lessphp/lessc.inc.php";
$colors = array("background"=>"bg-color", "primary"=>"brand-primary", "secondary"=>"brand-info", "accent"=>"brand-success");
$less = new lessc;
if( isset($_POST['grabar']) ){
    $colors = array_flip($colors);
    $compile = true;
    $values = array();
    if( substr($_POST['brand-primary'],0,1)=='#' ){
        $rgb = hex2rgb($_POST['brand-primary']);
    } else {
        $rgb = str_replace('rgb(','',$_POST['brand-primary']);
        $rgb = str_replace(')','',$rgb);
        $rgb = explode(",", $rgb);
    }
    $sum = 0;
    foreach( $rgb as $val ){
        $sum+=(int)$val;
    }
    if( $sum>382 ){
        $values["text-color"] = "#333";
    } else {
        $values["text-color"] = "#fff";
    }
    foreach($_POST as $field=>$color){
        /*
        $color = hex2rgb($color);
        $color = "rgb(".join(",",$color).")";
        //*/
        if( in_array($field, array_keys($colors)) ){
            $values[$field] = $color;
            $ins = array($color, $colors[$field]);
            $cur = $dbh->query("UPDATE config SET value=? WHERE name=?;",$ins);
            $compile &= $cur!==false;
        }
    }
    $colors = array_flip($colors);
    $font = $_POST['font'];
    if( is_array($font) ){
        foreach( $font as $type=>$url ){
            downloadFont($url, $type);
        }
    }

    if( $compile ){
        $handle = fopen(PATH."/html/css/theme-template.less", "r");
        $lessString = "";
        while( $line = fgets($handle) ){
            $lessString.=$line;
        }
        fclose($handle);
        //echo $lessString;exit;
        foreach( $values as $find=>$replace ){
            $lessString = str_replace("{{{$find}}}", $replace, $lessString);
        }
        $handle = fopen(PATH."/html/css/less/variables.less","w");
        fwrite($handle, $lessString);
        fclose($handle);
       
        //$less->compileFile(PATH."/html/css/less/bootstrap.less",PATH."/html/css/theme.css");
        header("Content-type: application/json; charset=UTF-8");
        print json_encode(array("ok"=>true));
        exit;
    }
}
$cur = $dbh->query("SELECT * FROM config WHERE type='color';");
if( !empty($cur) ){
    $tmp = array();
    foreach( $cur as $cfg ){
        if( in_array($cfg['name'], array_keys($colors)) ){
            $tmp[$colors[$cfg['name']]] = $cfg['value'];
        }
    }
    $view->set("colors", $tmp);
}
function downloadFont($url, $type){
    set_time_limit(0);
    //This is the file where we save the    information
    $fp = fopen (PATH.'/html/css/fonts/main-'.$type.'.ttf', 'w+');
    //Here is the file we are downloading, replace spaces with %20
    $ch = curl_init(str_replace(" ","%20",$url));
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    // write curl response to file
    curl_setopt($ch, CURLOPT_FILE, $fp); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // get curl response
    curl_exec($ch); 
    curl_close($ch);
    fclose($fp);
}
?>
