<?php

require_once("Record.php");

interface FileDB {

	public function getRecords(callable $filter = null, int $limit = 0, int $offset = 0);

	public function getParsedRecords($className);

	public function addRecord(Record $record);

	public function deleteRecord($id);

	public function restore($id): bool;

	public function updateRecord(Record $record);

	public function drop();

	public function commit();

	public function rollback();

}

?>