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
		$white = imagecolorallocate($this->image, 255, 255, 255);
		$this->image = imagerotate($this->image, $deg, $white);
	}

	public function getImage()
	{
		return $this->image;
	}
}
