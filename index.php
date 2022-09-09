<?php

include('./vendor/autoload.php');

use Volochaev\ImageGeneration\ImageGenerator;
use Volochaev\ImageGeneration\QR\QrGenerator;

$originalLocale = setlocale(LC_ALL, 0);

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
