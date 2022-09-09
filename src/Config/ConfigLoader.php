<?php

namespace Volochaev\ImageGeneration\Config;

use RuntimeException;

final class ConfigLoader
{

	private static $path = __DIR__ . '/../../.cfg';
	private static $pathDefault = __DIR__ . '/../../.default.cfg';
	private static $config = [];


	public static function cfg($key)
	{
		if (key_exists($key, static::$config)) {
			return static::$config[$key];
		}
		return '';
	}

	public static function loadConfig()
	{
		if (!self::configExists()) {
			self::createConfig();
		}
		$rawConfig = self::readFile(static::$path);
		if ($rawConfig === '' && self::defaultConfigExists()) {
			self::createConfig();
			$rawConfig = self::readFile(static::$path);
		}
		static::$config = self::parseConfig($rawConfig);
	}


	private static function parseConfig($rawConfig)
	{
		$config = [];
		$lines = explode(PHP_EOL, $rawConfig);
		foreach ($lines as $line) {
			$value = explode('=', $line);
			if (count($value) === 2) {
				$config[trim($value[0])] = trim($value[1]);
			}
		}

		return $config;
	}


	private static function createConfig()
	{
		if (!self::defaultConfigExists()) {
			throw new RuntimeException('No default cfg exists');
		}

		$streamDefaultCfg = fopen(self::$pathDefault, 'r');
		$streamCfg = fopen(self::$path, 'w+');
		try {
			$cfgSize = filesize(self::$pathDefault);
			$step = 512;
			$curStep = 0;
			while ($curStep < $cfgSize) {
				$buffer = fread($streamDefaultCfg, $step);
				fwrite($streamCfg, $buffer, $step);
				$curStep += $step;
			}
		} finally {
			fclose($streamDefaultCfg);
			fclose($streamCfg);
		}
	}


	private static function configExists()
	{
		return file_exists(self::$path);
	}


	private static function defaultConfigExists()
	{
		return file_exists(self::$pathDefault);
	}

	private static function readFile($path)
	{
		$size = filesize($path);
		$stream = fopen($path, 'r');
		$string = '';
		try {
			$step = 512;
			$curStep = 0;
			while ($curStep < $size) {
				$string .= fread($stream, $step);
				$curStep += $step;
			}
		} finally {
			fclose($stream);
		}
		return $string;
	}



	public static function addRule($key, $value)
	{
		static::$config[$key] = $value;
	}
}
