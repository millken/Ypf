<?php

namespace Ypf\Core;

abstract class Controller {
	public static $container;

	public function __construct() {
		self::$container = \Ypf\Ypf::getContainer();
	}

	protected function getCalledControllerName() {
		$qualifiedClassParts = explode('\\', get_called_class());
		return end($qualifiedClassParts);
	}

	protected function action($action, $args = array())	{
		$a = new \Ypf\Core\Action($action, $args);
		return $a->execute();
	}

	public function __set($name, $value) {
		self::$container[$name] = $value;
	}

	public function __get($name) {
		return self::$container[$name];
	}
}
