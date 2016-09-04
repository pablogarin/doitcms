<?php
class TableFactory {
    private $table;
    function __construct( $table ){
        if( empty($table) ){
            throw new Exception("You must define a table.");
            exit;
        }

    }
}
?>
