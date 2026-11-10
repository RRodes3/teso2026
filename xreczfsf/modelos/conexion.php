<?php

class Conexion{

	static public function conectar(){
		/* monaso 10/11/2026 */
		$link = new PDO("mysql:host=localhost;dbname=bdteso2027",
			            "root",
			            "");
		$link->exec("set names utf8");
		return $link;

	}

}