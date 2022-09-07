<?php

namespace Volochaev\ImageGeneration;

use Volochaev\ImageGeneration\Helpers\HexToRGB;
use Volochaev\ImageGeneration\Image;
class ImageGenerator 
{

	protected $white = '#fff';
	protected $black = '#000';
	protected $gray = '';


	public function __construct()
	{
		$now = microtime(true);
		for ($i = 0; $i < 10; $i++) {
			$image = new Image(512, 512, $this->white);
			$image->generateAndSave();
		}
		var_dump(microtime(true) - $now);
	}
}
