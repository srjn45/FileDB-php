<?php

class Util {

	public static function objectToObject($instance, $className) {
		return unserialize(sprintf(
			'O:%d:"%s"%s',
			strlen($className),
			$className,
			strstr(strstr(serialize($instance), '"'), ':')
		));
	}
}

?>