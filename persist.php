<?php

require_once('Records/User.php');



//$db = new JsonDB("user");

/*$users = $db->getParsedRecords("User");

foreach ($users as $user) {
	echo $user;
}*/

//LoggedJsonDB::$autoCommit = false;

$logDB = new LoggedJsonDB(new BasicLogRecord("user"));

$users = $logDB->getParsedRecords("User");

//print_r($users);

foreach ($users as $user) {
	echo $user."<br>";
}

$logs = $logDB->getParsedLogRecords("BasicLogRecord");
echo "<br><br>Logs:<br>";
foreach ($logs as $log) {
	echo $log."<br>";
}

//echo $logDB;

//echo $db->deleteRecord(2);
$user = new User();
$user->setId(5);
$user->setUserName("user");
$user->setPasswd("pass");
$user->setDateAdded(date("Y-m-d H:i:s"));
$user->setDateModified(date("Y-m-d H:i:s"));

//$logDB->addRecord($user);
//$logDB->updateRecord($user);
//$logDB->commit();
//$logDB->deleteRecord(1);

echo "<br>".$user;
//echo "<br>".gettype($user);
//$db->updateRecord($user);
//$db->addRecord($user);
//$db->drop();

//echo $db->getRecords();

//echo $db;

//var_dump($props);
?>