<?php
/*
 *  DATABASE HELPER.
 *  CLASE DE CONTROL DE QUERYS A LA BASE DE DATOS Y SUS METODOS DE CONEXION.
 * */
define("SQLITE", 1);
define("MYSQL", 2);
class SQLHelper extends PDO{
    private $mode, $connectionString, $error, $lastId;

    public function __construct( $mode = MYSQL ){
        $this->mode = $mode;
        switch( $mode ){
        case 1: // SQLITE
            $this->connectionString = 'sqlite:'.PATH.'/db/db.sqlite'; // SQLITE
            try{
                parent::__construct($this->connectionString);
            } catch( Exception $ex ){
                print $ex->getMessage();
                print "<br/>\n";
                exit("Ruta invalida para el archivo SQLITE.");
            }
            break;
        case 2: // MYSQL
            $filename = PATH.'/configs/database.conf';
            $handle = fopen($filename,"r");
            $configs = fread($handle, filesize($filename));
            fclose($handle);
            $configs = unserialize($configs);
            $this->connectionString = 'mysql:host='.$configs['host'].';dbname='.$configs['dbname'].';charset=utf8mb4'; // MYSQL
            try{
                parent::__construct($this->connectionString, $configs['username'], $configs['password']);
                $this->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);
            } catch( Exception $ex ){
                print $ex->getMessage();
                print "<br/>\n";
                echo "Datos invalidos de conexi&oacute;n.";
                exec("unlink $filename");
                exit;
            }
            break;
        }
        $this->checkDatabase();
    }
    public function query($query,$data=null){
        $result = array();
        $tmp = (explode(' ', $query));
        $command = $tmp[0];
        $command = trim($command);
        $mod = (strtolower($command) == 'insert' or strtolower($command)=='update');
        if( $query==null || empty($query) ){
            return false;
        }
        $cur = $this->prepare($query);
        $this->error = $this->errorInfo()[2];
        if( !empty($this->error) ){
            return false;
        }
        if( $exec = $cur->execute($data) ){
            if( $mod ){
                $result = $this->lastInsertId();
            } else {
                while( $row = $cur->fetch(PDO::FETCH_ASSOC) ){
                    $result[] = $row;
                }
            }
        } else {
            $this->error = $this->errorInfo()[2];
            return false;
        }
        return $result;
    }
    public function getLastInsertId()
    {
        return $this->lastInsertId();
    }
    public static function getDBType()
    {
        return $this->mode;
    }
    private function checkDatabase()
    {
        $res = $this->query("show tables;");
        if( empty($res) ){
            $this->createTables();
        }
    }
    private function createTables()
    {
        $cmds = array();
        $this->i = 0;

        // leer script de creacion de tablas
        switch( $this->mode ){
        case 1:
            $this->readSQLFile($cmds, PATH."/database/script.sqlite");
            break;
        case 2:
            $this->readSQLFile($cmds, PATH."/database/tables.sql");
            break;
        }
        // leer archivo con zonas
        $this->readSQLFile($cmds, PATH."/database/zonas.sql");

        foreach( $cmds as $cmd ){
            $create = $this->exec($cmd);
            if( $create === false ){
                $this->error = $this->errorInfo()[2];
                echo "Error: ".$this->error."<br/>\n";
                echo $cmd."<br/>\n";
                exit;
            }
            $this->error = $this->errorInfo()[2];
            if( !empty($this->error) ){
                print_r("Error: ".$this->error);
                var_dump($create);
            }
        }
    }

    private function readSQLFile(&$commandList, $source)
    {
        $handle = fopen($source, "r");
        while( $line = fgets($handle) ){
            if( !isset($commandList[$this->i]) ){
                $commandList[$this->i] = "";
            }
            $commandList[$this->i] .= "$line";
            if( (trim($line)==');') OR (trim($line)=='-- GO')){
                $this->i++;
            }
        }
        fclose($handle);
    }
}
$dbh = new SQLHelper(MYSQL);
?>
