<?php

require_once("LoggedFileDB.php");
require_once("LogRecord.php");
require_once("Util.php");

class LoggedJsonDB implements LoggedFileDB{

	public static $LOG_GET_RECORD = false;
	public static $LOG_ADD_RECORD = true;
	public static $LOG_UPDATE_RECORD = true;
	public static $LOG_DELETE_RECORD = true;
	public static $LOG_DROP = true;

	public static $GET_RECORD = "fetch";
	public static $ADD_RECORD = "added";
	public static $UPDATE_RECORD = "update";
	public static $DELETE_RECORD = "delete";
	public static $DROP = "drop";

	public static $autoCommit = true;
	public static $dbPath = "db/";

	public static $logFileName = "_log";

	private $mainFile;
	private $logFile;
	private $logRecord;

	public function __construct(LogRecord $logRecord){
		$this->mainFile = new JsonDB($logRecord->getFileName());
		$this->logFile = new JsonDB(self::$logFileName);

		$this->mainFile::$autoCommit = self::$autoCommit;
		$this->logFile::$autoCommit = self::$autoCommit;

		$this->mainFile::$dbPath = self::$dbPath;
		$this->logFile::$dbPath = self::$dbPath;
		
		$this->logRecord = $logRecord;
	}

	public function getRecords() {
		if(self::$LOG_GET_RECORD){
			$this->logRecord->setOperation(self::$GET_RECORD);
			$this->logFile->addRecord($this->logRecord);
		}
		return $this->mainFile->getRecords();
	}

	public function getParsedRecords($className){
		if(self::$LOG_GET_RECORD){
			$this->logRecord->setOperation(self::$GET_RECORD);
			$this->logFile->addRecord($this->logRecord);
		}
		return $this->mainFile->getParsedRecords($className);
	}

	public function getLogRecords() {
		return $this->logFile->getRecords();
	}

	public function getParsedLogRecords($className){
		return $this->logFile->getParsedRecords($className);
	}

	public function addRecord(Record $record) {
		$this->mainFile->addRecord($record);
		if(self::$LOG_ADD_RECORD){
			$this->logRecord->setOperation(self::$ADD_RECORD);
			$this->logFile->addRecord($this->logRecord);
		}
	}

	public function deleteRecord($id){
		if($this->mainFile->deleteRecord($id)){
			if(self::$LOG_DELETE_RECORD){
				$this->logRecord->setOperation(self::$DELETE_RECORD);
				$this->logFile->addRecord($this->logRecord);
			}
			return true;
		}
		return false;
	}
	public function updateRecord(Record $record){
		if($this->mainFile->updateRecord($record)){
			if(self::$LOG_UPDATE_RECORD){
				$this->logRecord->setOperation(self::$UPDATE_RECORD);
				$this->logFile->addRecord($this->logRecord);
			}
			return true;
		}
		return false;
	}

	public function drop() {
		$this->mainFile->drop();
		if(self::$LOG_DROP){
			$this->logRecord->setOperation(self::$DROP);
			$this->logFile->addRecord($this->logRecord);
		}
	}

	public function commit() {
		$this->mainFile->commit();
		$this->logFile->commit();
	}

	public function rollback() {
		$this->mainFile->rollback();
		$this->logFile->rollback();
	}

	public function __toString() {
		return json_encode(get_object_vars($this));
	}
}

?>