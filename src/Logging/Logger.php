<?php

namespace Volochaev\ImageGeneration\Logging;


class Logger
{

	private static $instance = null;

	private $accept = [

	];

	private $stream;

	private function __construct($channel)
	{
		switch ($channel) {
			case 'file':
				$path = __DIR__ . '/../../logs/log-' . date('Y-m-d_H:i:s') . '.log';
				$this->stream = fopen($path, 'w+');
				break;
			case 'console':
				$path = 'php://stdout';
				$this->stream = fopen($path, 'w');
				break;
		}
	}


	public static function getInstance($channel = 'console')
	{
		if (static::$instance === null) {
			static::$instance = new Logger($channel);
			return static::$instance;
		};
		return static::$instance;
	}


	public function setAccept($accept)
	{
		$this->accept = $accept;
	}


	public function warn($string)
	{
		if (in_array('WARN', $this->accept)) {
			$string = $this->getTemplate('warn') . $string . PHP_EOL;
			fwrite($this->stream, $string);
		}
	}


	public function info($string)
	{
		if (in_array('INFO', $this->accept)) {
			$string = $this->getTemplate('info') . $string . PHP_EOL;
			fwrite($this->stream, $string);
		}
	}


	public function error($string)
	{
		if (in_array('ERROR', $this->accept)) {
			$string = $this->getTemplate('error') . $string . PHP_EOL;
			fwrite($this->stream, $string);
		}
	}


	private function __destruct()
	{
		fclose($this->stream);
	}


	private function getTemplate($scope)
	{
		return strtoupper($scope) . ": " . date('Y-m-d_H:i:s') . ": ";
	}
}
