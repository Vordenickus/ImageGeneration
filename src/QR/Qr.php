<?php

namespace Volochaev\ImageGeneration\QR;


use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Volochaev\ImageGeneration\Helpers\HexToRGB;

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

		$this->image = imagecrop( // отрезаем часть после смещения
			$this->image,
			[
				'x'=> $deltaWidth / 2, // Смещение вправо
				'y' => $deltaHeight / 2, // Смещение вниз
				'width' => $width,
				'height' => $height
			]
		);
	}


	/**
	 * @var float $factor фактор скалирования
	 */
	public function rescale($factor)
	{
		$width = imagesx($this->image);
		$height = imagesy($this->image);
		$width *= $factor;
		$height *= $factor;
		return imagescale($this->image, $width, $height);
	}


	public function getImage()
	{
		return $this->image;
	}
}
