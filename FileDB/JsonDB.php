<?php

require_once("FileDB.php");
require_once("Util.php");

class JsonDB implements FileDB {

	public static $autoCommit = true;
	public static $dbPath     = "db/";
	public static $softDelete = false;

	private $fileName;
	private $dbFilePath;
	private $id;
	private $count;
	private $records;

	// In-memory index: record id => position in $records array (O(1) lookup)
	private $index = [];

	public function __construct($fileName) {
		$this->fileName = $fileName;
		$this->id       = 0;
		$this->count    = 0;
		$this->records  = [];

		if (!file_exists(self::$dbPath)) {
			if (!mkdir(self::$dbPath, 0755, true)) {
				throw new RuntimeException("FileDB: cannot create directory: " . self::$dbPath);
			}
		}

		$this->dbFilePath = self::$dbPath . $this->fileName . ".json";

		if (file_exists($this->dbFilePath)) {
			$this->rollback();
		} else {
			$this->commit();
		}
	}

	// -------------------------------------------------------------------------
	// Read
	// -------------------------------------------------------------------------

	public function getRecords(callable $filter = null, int $limit = 0, int $offset = 0) {
		$result = $this->records;

		// Exclude soft-deleted records unless caller provides their own filter
		if (self::$softDelete && $filter === null) {
			$result = array_values(array_filter($result, function ($r) {
				$deleted = is_object($r) ? ($r->deletedAt ?? null) : null;
				return $deleted === null;
			}));
		}

		if ($filter !== null) {
			$result = array_values(array_filter($result, $filter));
		}

		if ($offset > 0) {
			$result = array_slice($result, $offset);
		}

		if ($limit > 0) {
			$result = array_slice($result, 0, $limit);
		}

		return $result;
	}

	public function getParsedRecords($className) {
		$recordArr = [];
		foreach ($this->records as $record) {
			$obj = Util::objectToObject($record, $className);
			if ($obj instanceof SecureRecord) {
				$secure = new $className();
				$obj->setSecure($secure->getSecure());
			}
			array_push($recordArr, $obj);
		}
		return $recordArr;
	}

	public function countRecords(callable $filter = null): int {
		$result = self::$softDelete
			? array_filter($this->records, fn($r) => ($r->deletedAt ?? null) === null)
			: $this->records;

		if ($filter !== null) {
			$result = array_filter($result, $filter);
		}
		return count($result);
	}

	// -------------------------------------------------------------------------
	// Write
	// -------------------------------------------------------------------------

	public function addRecord(Record $record) {
		$this->id++;
		$this->count++;
		$record->setId($this->id);
		$record->setDateAdded(date("Y-m-d H:i:s"));
		$record->setDateModified(date("Y-m-d H:i:s"));

		$this->records[] = $record;
		$this->index[$record->getId()] = array_key_last($this->records);

		if (self::$autoCommit) {
			$this->commit();
		}
	}

	public function deleteRecord($id) {
		if (!isset($this->index[$id])) {
			return false;
		}

		if (self::$softDelete) {
			$pos    = $this->index[$id];
			$record = $this->records[$pos];
			if (is_object($record) && method_exists($record, 'setDeletedAt')) {
				$record->setDeletedAt(date("Y-m-d H:i:s"));
			} else {
				$record->deletedAt = date("Y-m-d H:i:s");
			}
			$this->records[$pos] = $record;
		} else {
			array_splice($this->records, $this->index[$id], 1);
			$this->count--;
			$this->rebuildIndex();
		}

		if (self::$autoCommit) {
			$this->commit();
		}
		return true;
	}

	public function restore($id): bool {
		if (!isset($this->index[$id])) {
			return false;
		}
		$record = $this->records[$this->index[$id]];
		if (is_object($record) && method_exists($record, 'setDeletedAt')) {
			$record->setDeletedAt(null);
		} else {
			$record->deletedAt = null;
		}
		if (self::$autoCommit) {
			$this->commit();
		}
		return true;
	}

	public function updateRecord(Record $record) {
		if (!isset($this->index[$record->getId()])) {
			return false;
		}
		$pos = $this->index[$record->getId()];
		$record->setDateAdded(
			Util::objectToObject($this->records[$pos], "BasicRecord")->getDateAdded()
		);
		$record->setDateModified(date("Y-m-d H:i:s"));
		$this->records[$pos] = $record;
		// index position unchanged on update; no rebuild needed

		if (self::$autoCommit) {
			$this->commit();
		}
		return true;
	}

	public function drop() {
		$this->id      = 0;
		$this->count   = 0;
		$this->records = [];
		$this->index   = [];

		if (self::$autoCommit) {
			$this->commit();
		}
	}

	// -------------------------------------------------------------------------
	// Persistence
	// -------------------------------------------------------------------------

	public function commit() {
		$file = fopen($this->dbFilePath, "w");
		if ($file === false) {
			throw new RuntimeException("FileDB: cannot open for writing: " . $this->dbFilePath);
		}
		$bytes = fwrite($file, $this);
		fclose($file);
		if ($bytes === false) {
			throw new RuntimeException("FileDB: write failed: " . $this->dbFilePath);
		}
	}

	public function rollback() {
		$file = fopen($this->dbFilePath, "r");
		if ($file === false) {
			throw new RuntimeException("FileDB: cannot open for reading: " . $this->dbFilePath);
		}
		$size     = filesize($this->dbFilePath);
		$contents = ($size > 0) ? fread($file, $size) : '';
		fclose($file);
		if ($contents === false) {
			throw new RuntimeException("FileDB: read failed: " . $this->dbFilePath);
		}
		$this->jsonToThis($contents);
	}

	protected function jsonToThis($jsonStr) {
		if (empty($jsonStr)) {
			return;
		}
		$obj = json_decode($jsonStr);
		if ($obj === null) {
			throw new RuntimeException("FileDB: corrupt JSON in: " . $this->dbFilePath);
		}
		$this->id      = $obj->id;
		$this->count   = $obj->count;
		$this->records = $obj->records;
		$this->rebuildIndex();
	}

	public function __toString() {
		return json_encode([
			'id'      => $this->id,
			'count'   => $this->count,
			'records' => $this->records,
		]);
	}

	// -------------------------------------------------------------------------
	// Internal helpers
	// -------------------------------------------------------------------------

	private function rebuildIndex(): void {
		$this->index = [];
		foreach ($this->records as $key => $record) {
			if (is_object($record) && isset($record->id)) {
				$this->index[$record->id] = $key;
			}
		}
	}
}
