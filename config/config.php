<?php

if(!defined('APP_RAN')) { die(); }

define('DB_SERVER', '');
define('DB_NAME', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_USERNAMESEL', '');
define('DB_PASSWORDSEL', '');

class DBR {
	private $handle;
	
	public function __construct() {
		try {
			$this->handle = new mysqli(DB_SERVER, DB_USERNAMESEL, DB_PASSWORDSEL, DB_NAME);
		} catch (exception $e) {
		}
	}
	
	public function read($query) {
	    if ($result = $this->handle->prepare($query)) {
			$result = $this->handle->query($query)->fetch_all(MYSQLI_ASSOC);
		} else {
			$result = [];
    	}
    	return $result;
	}
	
	function __destruct() {
		try {
		    $this->handle->close();
		    $this->handle = null;
		} catch (exception $e) {
		}
	}
}

class DBW {
	private $handle;
	
	public function __construct() {
		try {
			$this->handle = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		} catch (exception $e) {
		}
	}
	
	public function write($query) {
		$this->handle->query($query);
		$insert_id = $this->handle->insert_id;
		return $insert_id;
	}
	
	function __destruct() {
		try {
		    $this->handle->close();
		    $this->handle = null;
	   	} catch (exception $e) {
		}
	}
}

function readdb($query) {
	$dbr = new DBR();
	return $dbr->read("$query");
}

?>
