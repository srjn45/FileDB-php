<?php

require_once("BasicRecord.php");
require_once("LogRecord.php");

class BasicLogRecord extends BasicRecord implements LogRecord{

	public $fileName;
	public $operation;

	public function __construct($fileName) {
		$this->fileName = $fileName;
	}

	public function getFileName(){
		return $this->fileName;
	}

	public function setFileName($fileName){
		$this->fileName = $fileName;
	}

	public function getOperation(){
		return $this->operation;
	}

	public function setOperation($operation){
		$this->operation = $operation;
	}

	public function __toString() {
		return json_encode(get_object_vars($this));
	}
}

?>