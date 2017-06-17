<?php

require_once("Record.php");

class BasicRecord implements Record {

	public $id;
	public $dateAdded;
	public $dateModified;

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	function getDateAdded(){
		return $this->dateAdded;
	}

	function setDateAdded($dateAdded){
		$this->dateAdded = $dateAdded;
	}
	
	function getDateModified(){
		return $this->dateModified;
	}

	function setDateModified($dateModified){
		$this->dateModified = $dateModified;
	}

	public function __toString() {
		return json_encode(get_object_vars($this));
	}
}

?>