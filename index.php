<?php

use Volochaev\ImageGeneration\ImageGenerator;

include('./vendor/autoload.php');

ini_set('memory_limit', '256M');

error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);

$image = new ImageGenerator();
