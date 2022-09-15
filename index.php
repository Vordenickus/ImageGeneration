<?php

include('./vendor/autoload.php');

use Volochaev\ImageGeneration\Config\ArgumentsParser;
use Volochaev\ImageGeneration\Config\ConfigLoader;
use Volochaev\ImageGeneration\ImageGenerator;
use Volochaev\ImageGeneration\Logging\Logger;

$logger = Logger::getInstance('console');

$logger->setAccept(['ERROR', 'INFO', 'WARN']);

$originalLocale = setlocale(LC_ALL, 0);

ConfigLoader::loadConfig();
ArgumentsParser::parse($argv);

setConfig();

try {
	$image = new ImageGenerator();
	$image->generateDataset();
} finally {
	configReturn($originalLocale);
}

function setConfig()
{
	error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);
	setlocale(LC_ALL, "C");
}

function configReturn($locale)
{
	setlocale(LC_ALL, $locale);
}
