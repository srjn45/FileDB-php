<?php

class Util {

	public static function objectToObject($instance, $className) {
		$assoc  = json_decode(json_encode($instance), true);
		$target = (new ReflectionClass($className))->newInstanceWithoutConstructor();
		foreach ($assoc as $key => $value) {
			if (property_exists($target, $key)) {
				$target->$key = $value;
			}
		}
		return $target;
	}
}

?>