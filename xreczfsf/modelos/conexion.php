<?php

class Conexion{

	static public function conectar(){

		$link = new PDO("mysql:host=localhost;dbname=bdteso2027",
			            "root",
			            "");
		$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);	/* RRR - 12-11-2026 PDO SILENT se encarga de omitir el error de llamado de tabla "Usuarios"*/
		$link->exec("set names utf8");
		return $link;

	}

}