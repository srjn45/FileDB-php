<?php

require_once("Record.php");

interface LogRecord extends Record {

	public function getFileName();
	public function setFileName($fileName);

	public function getOperation();
	public function setOperation($operation);

}

?>