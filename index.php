<?php

use Volochaev\ImageGeneration\ImageGenerator;

include('./vendor/autoload.php');

define("TOP",0);
define("BOTTOM",1);
define("LEFT",2);
define("RIGHT",3);
error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);

$image = new ImageGenerator();
