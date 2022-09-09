<?php

namespace Volochaev\ImageGeneration\QR;


use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;


class Qr
{
	protected $image;
	protected $hash;


	public function __construct($hash)
	{
		$this->hash = $hash;
		$this->image = $this->generate();
	}

	protected function generate()
	{
		$qrImage = null;
		$renderer = new ImageRenderer(
			new RendererStyle(81),
			new ImagickImageBackEnd()
		);
		$writer = new Writer($renderer);
		$qrImage = $writer->writeString($this->hash);

		$gdImage = imagecreatefromstring($qrImage);

		return $gdImage;
	}


	public function rotate($deg)
	{
		$width = imagesx($this->image);
		$height = imagesy($this->image);
		$white = imagecolorallocate($this->image, 255, 255, 255);
		$this->image = imagerotate($this->image, $deg, $white);
		$newWidth = imagesx($this->image);
		$newHeight = imagesy($this->image);
		$deltaWidth = abs($width - $newWidth);
		$deltaHeight = abs($height - $newHeight);
		$this->image = imagecrop(
			$this->image,
			[
				'x'=> $deltaWidth / 2,
				'y' => $deltaHeight / 2,
				'width' => $width,
				'height' => $height
			]
		);
	}

	public function getImage()
	{
		return $this->image;
	}
}
