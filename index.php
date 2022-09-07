<?php

use Volochaev\ImageGeneration\ImageGenerator;

include('./vendor/autoload.php');

ini_set('memory_limit', '1024M');

error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);

$image = new ImageGenerator();
