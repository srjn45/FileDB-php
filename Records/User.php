<?php

require_once('FileDB/RequireFileDB.php');

class User extends BasicSecureRecord{
	
	public $userName;
	public $passwd;

	function getUserName(){
		return $this->userName;
	}

	function setUserName($userName){
		$this->userName = $userName;
	}

	function getPasswd(){
		if($this->passwd == null){
			return $this->passwd;
		}
		$decode = new $this->secure();
		return $decode->decode($this->passwd);
	}

	function setPasswd($passwd){
		$encode = new $this->secure();
		$this->passwd = $encode->encode($passwd);
	}

	public function __toString() {
		return json_encode(get_object_vars($this));
	}
}

?>