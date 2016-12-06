<?php
/*
 *  ARREGLO CON LOS DETALLES DEL ARMADO DEL MENU.
 * */
$navs = array(
    "configs" => array(
        "name" => "Configuraciones",
        "icon" => "fa-cogs",
        "url" => "admin/configuraciones",
        "current" => ($page=="configuraciones")
    ),
    "secciones" => array(
        "name" => "Secciones",
        "icon" => "fa-th-list",
        "url" => "admin/secciones",
        "current" => ($page=="secciones")
    ),
    "vistas" => array(
        "name" => "Vistas",
        "icon" => "fa-eye",
        "url" => "admin/vistas",
        "current" => ($page=="vistas")
    )
);
//*
$sections = $dbh->query("SELECT * FROM template where id>0;");
if( !empty($sections) ){
    $tmp = array();
    foreach( $sections as $row ){
        $tmp[$row['id']] = array(
            "id" => $row['id'],
            "name" => $row['name']
        );
    }
    $navs['vistas']['navs'] = $tmp;
}
//*/
$view->set("navs", $navs);
?>
