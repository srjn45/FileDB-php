<?php

require_once("FileDB.php");

interface LoggedFileDB extends FileDB {

	public function getLogRecords();

	public function getParsedLogRecords($className);

}

?>