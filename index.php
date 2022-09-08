<?php

use Volochaev\ImageGeneration\ImageGenerator;

include('./vendor/autoload.php');

error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);

$image = new ImageGenerator();
$image->generateDataset(30);
