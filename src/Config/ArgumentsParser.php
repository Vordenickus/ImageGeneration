<?php

namespace Volochaev\ImageGeneration\Config;

class ArgumentsParser
{

	private const KEYS = [
		'--v',
		'-v'
	];

	private static $args;

	public static function parse($args)
	{
		static::$args = static::populate($args);
	}


	private static function populate($args)
	{
		foreach ($args as $arg) {
			if (in_array($arg, static::KEYS)) {
				if ($arg === '-v' || $arg === '--v') {
					ConfigLoader::addRule('VERBOSE', true);
				}
			}
		}
	}
}
