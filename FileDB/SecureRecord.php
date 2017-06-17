<?php

require_once("Record.php");

interface SecureRecord extends Record {
	
	function getSecure();
	function setSecure($secure);
    
}

?>