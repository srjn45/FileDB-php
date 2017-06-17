<?php

interface Record {
	
	function getId();
	function setId($id);

	function getDateAdded();
	function setDateAdded($dateAdded);
	
	function getDateModified();
	function setDateModified($dateModified);

}

?>