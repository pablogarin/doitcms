<?php
$login = true;
if( isset($_REQUEST['logout']) ){
    unset($_SESSION);
    unset($_COOKIE);
    setcookie(COOKIE, null, time()-(3600*24*365));
    header("Location: /admin/");
    exit;
}
if( !empty($_REQUEST) ){
    foreach( $_REQUEST as $k=>$v ){
        $view->set($k,$v);
    }
    if( isset($_POST['username']) && isset($_POST['password']) ){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $res = $dbh->query("SELECT * FROM usuario WHERE username=? AND password=? AND active=1;",array($username,$password));
        if( ($dbh->errorInfo()[2])!=null || empty($res[0]) ){
            $view->set("error",true);
        } else {
            $login = false;
            if( isset($_POST['remember']) ){
                $cookie_value = md5(date("Y-m-d H:i:s"));
                $dbh->query("UPDATE usuario SET cookie=? WHERE id=?;",array($cookie_value, $res[0]['id']));
                setcookie(COOKIE, $cookie_value, time()+(3600*24*365));
            } else {
                $_SESSION['user'] = $res[0]['id'];
            }
        }
    }
}
if( isset($_COOKIE[COOKIE]) ){
    $cookie = $_COOKIE[COOKIE];
    $query = $dbh->query("SELECT * FROM usuario WHERE cookie=? AND active=1;",array($cookie));
    if( ($dbh->errorInfo()[2])==null && !empty($query[0]) ){
        $login = false;
    }
}
if( isset($_SESSION['user']) && !empty($_SESSION['user']) ){
    $id = $_SESSION['user'];
    $query = $dbh->query("SELECT * FROM usuario WHERE id=? AND active=1;",array($id));
    if( ($dbh->errorInfo()[2])==null && !empty($query[0]) ){
        $login = false;
    }
}
if( $login ){
    $view->setTemplate("admin/login.html");
    print $view->getView();
    exit;
}
?>
