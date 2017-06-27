<?php
namespace App\Libraries\Network;

class Host { 
	
	public static function name() {
		return $_SERVER['HTTP_HOST'];
	}

}