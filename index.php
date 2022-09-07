<?php

use Volochaev\ImageGeneration\ImageGenerator;

include('./vendor/autoload.php');

ini_set('memory_limit', '1024M');

define("TOP",0);
define("BOTTOM",1);
define("LEFT",2);
define("RIGHT",3);
error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);

$image = new ImageGenerator();
