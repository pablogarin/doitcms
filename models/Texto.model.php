<?php namespace Modelos;
class Texto extends Model{
	public $insert = "INSERT INTO texto VALUES(?,?,?,?,?,?,?);";
	public $update = "UPDATE texto SET titulo=?,cuerpo=?,llave=?,idioma=?,activo=?,idTipo=? WHERE id=?;";
	public $delete = "DELETE FROM texto WHERE id=?;";
	public $create = "
		CREATE TABLE texto(
			id integer primary key autoincrement not null,
			titulo varchar(255) not null,
			cuerpo text not null,
			llave varchar(120),
			idioma char(2) default 'ES',
			activo integer default 1, /* 1si, 0 no */
			idTipo integer
		);
	";
	function __construct($Sql,$values=null){
		parent::__construct($Sql,"texto");
		if($values!=null){
			$this->setValues($values);
		}
	}
	function setValidFields(){
		$this->validFields = array(
			"id" => array(
				"nombre"=>"id",
				"null" => true
			),
			"titulo" => array(
				"nombre" => "titulo",
                "label" => "Titulo",
				"type" => "text",
				"null" => false
			),
			"cuerpo" => array(
				"nombre"=>"cuerpo",
                "label" => "Cuerpo del texto",
				"type" => "ckeditor",
				"null" => false
			),
			"llave" => array(
				"nombre"=>"llave",
                "label" => "Llave",
				"type" => "text",
				"null" => true
			),
			"idioma" => array(
				"nombre"=>"idioma",
				"null" => true
			),
			"activo" => array(
				"nombre"=>"activo",
                "label" => "¿Activo?",
				"type" => "boolean",
				"null" => true
			),
			"idTipo" => array(
				"nombre"=>"idTipo",
				"null" => true
			)
		);
	}
	function setForeign($elements){
		//TODO: 
	}
}
