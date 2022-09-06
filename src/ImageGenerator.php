<?php

namespace Volochaev\ImageGeneration;

class ImageGenerator 
{
	public function __construct()
	{
		$image = new \Volochaev\ImageGeneration\Image(512,512, [255,255,255]);

		$image->generateAndSave();

	}
}
