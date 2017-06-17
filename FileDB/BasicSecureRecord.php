<?php

require_once("BasicRecord.php");
require_once("SecureRecord.php");

class BasicSecureRecord extends BasicRecord implements SecureRecord {

	protected $secure;

	public function __construct(){
		$this->secure = "McryptSecure";
	}

	function getSecure(){
		return $this->secure;
	}

	function setSecure($secure){
		$this->secure = $secure;
	}
    
	public function __toString() {
		return json_encode(get_object_vars($this));
	}
}

?>