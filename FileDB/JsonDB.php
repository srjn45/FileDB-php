<?php

require_once("FileDB.php");
require_once("Util.php");

class JsonDB implements FileDB{

	public static $autoCommit = true;

	public static $dbPath = "db/";

	private $fileName;
	private $dbFilePath;
	private $id;
	private $count;
	private $records;
	
	public function __construct($fileName) {

		$this->fileName = $fileName;
		$this->id = 0;
		$this->count = 0;
		$this->records = [];
		
		if(!file_exists(self::$dbPath)){
			mkdir(self::$dbPath);
		}

		$this->dbFilePath = self::$dbPath.$this->fileName.".json";

		if (file_exists($this->dbFilePath)) {
			$this->rollback();
		} else {
			$this->commit();
		}
	}

	public function getRecords() {
		return $this->records;
	}

	public function getParsedRecords($className){
		$recordArr = [];
		foreach ($this->records as $record) {
			$obj = Util::objectToObject($record, $className);
			if($obj instanceOf SecureRecord){
				$secure = new $className();
				$obj->setSecure($secure->getSecure());
			}
			array_push($recordArr, $obj);
		}
		return $recordArr;
	}

	public function addRecord(Record $record) {
		$this->id++;
		$this->count++;
		$record->setId($this->id);
		$record->setDateAdded(date("Y-m-d H:i:s"));
		$record->setDateModified(date("Y-m-d H:i:s"));

		array_push($this->records, $record);

		if(self::$autoCommit){
			$this->commit();
		}
	}

	public function deleteRecord($id){
		$remove = -1;
		foreach ($this->records as $key => $record) {
			if($record->id == $id){
				$remove = $key;
				break;
			}
		}
		if($remove >= 0){
			array_splice($this->records, $remove,1);
			$this->count--;

			if(self::$autoCommit){
				$this->commit();
			}
			return true;
		} else {
			return false;
		}
	}

	public function updateRecord(Record $record){

		$update = -1;

		foreach ($this->records as $key => $value) {
			if($value->id == $record->id){
				$update = $key;
				break;
			}
		}
		if ($update>=0){
			$record->setDateAdded(Util::objectToObject($this->records[$update],"BasicRecord")->getDateAdded());
			$record->setDateModified(date("Y-m-d H:i:s"));

			$this->records[$update] = $record;

			if(self::$autoCommit){
				$this->commit();
			}
			return true;
		}
		return false;
			
	}

	public function drop() {
		$this->id = 0;
		$this->count = 0;
		$this->records = [];

		if(self::$autoCommit){
			$this->commit();
		}
	}

	public function commit() {
		$file = fopen($this->dbFilePath, "w");
		fwrite($file, $this);
		fclose($file);
	}

	public function rollback() {
		$file = fopen($this->dbFilePath, "r");
		$this->jsonToThis(fread($file, filesize($this->dbFilePath)));
		fclose($file);
	}

	protected function jsonToThis($jsonStr) {
		$obj = json_decode($jsonStr);
		$this->id = $obj->id;
		$this->count = $obj->count;
		$this->records = $obj->records;
	}

	public function __toString() {
		return json_encode(get_object_vars($this));
	}

}

?>