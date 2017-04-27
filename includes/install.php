<?php
//exit(phpinfo());
if( !file_exists(PATH.'/configs/database.conf') ){
    if( isset($_REQUEST['grabar']) ){
        // save database data to a config file for future reference
        $data = array(
            "host" => $_REQUEST['host'],
            "dbname" => $_REQUEST['dbname'],
            "username" => $_REQUEST['username'],
            "password" => $_REQUEST['password']
        );
        $filename = PATH.'/configs/database.conf';
        $handle = fopen($filename,'w');
        fwrite($handle, serialize($data));
        fclose($handle);
        header("Location: /");
        exit;
    }
    $view = new View();
    $view->setFolder(PATH."/templates/");
    $view->setTemplate("install.html");
    print $view->getView();
    exit;
}
