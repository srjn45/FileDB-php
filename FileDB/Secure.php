<?php

interface Secure {

	public function encode($value);
	public function decode($value);

    public function getKey();
    public function setKey($key);
}

?>