<?php

require_once("Record.php");

interface FileDB {

	public function getRecords();

	public function getParsedRecords($className);

	public function addRecord(Record $record);

	public function deleteRecord($id);

	public function updateRecord(Record $record);

	public function drop();

	public function commit();

	public function rollback();

}

?>