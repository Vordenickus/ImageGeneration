<?php

include('./vendor/autoload.php');

use Volochaev\ImageGeneration\Config\ArgumentsParser;
use Volochaev\ImageGeneration\Config\ConfigLoader;
use Volochaev\ImageGeneration\ImageGenerator;

$originalLocale = setlocale(LC_ALL, 0);

ConfigLoader::loadConfig();
ArgumentsParser::parse($argv);

setConfig();

try {
	$image = new ImageGenerator();
	$image->generateDataset(30);
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
