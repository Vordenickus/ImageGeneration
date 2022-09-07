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
		for ($i = 0; $i < 1; $i++) {
			$image = new Image(512, 512, $this->white);
			$image->generateAndSave();
		}
	}
}
