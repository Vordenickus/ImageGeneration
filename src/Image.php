<?php

namespace Volochaev\ImageGeneration;

use Volochaev\ImageGeneration\Figures\RightTriangle;
use Volochaev\ImageGeneration\Figures\Square;

use Volochaev\ImageGeneration\Helpers\HexToRGB;
use Volochaev\ImageGeneration\Helpers\Perspective;

class Image
{
	public $width;
	public $height;
	protected $background;
	protected $image;
	protected $figures = [];
	protected $rotateChance = 20;
	public $pivot;
	protected $occupied = ['x' => [], 'y' => []];

	public function __construct($width, $height, $background)
	{
		$this->width = $width;
		$this->height = $height;
		$this->image = imagecreatetruecolor($this->width, $this->height);
		$this->pivot = imagecreatefrompng(__DIR__ . '/../ideal/scan-mark.png');
		$this->background = $this->allocateCollor($this->image, $background);
		
		imagefill($this->image, 0, 0, $this->background);
		$this->addPivots();
		$this->addFigure('f');
		$this->drawFigures();
	}

	public function addFigure($figure)
	{
		for ($i = 0; $i < 6; $i++) {
			$this->figures[] = $this->getRandomTriangle();
		}

		for ($i = 0; $i < 6; $i++) {
			$this->figures[] = $this->getRandomSquare();
		}
	}

	public function addPivots()
	{
		$deg = [0, 90, 180, 270];
		$white = $this->allocateCollor($this->image, '#fff');
		for($i = 0; $i < 4; $i++) {
			$sx = imagesx($this->pivot);
			$sy = imagesy($this->pivot);
			$x = $this->getRandomX($this->width - $sx);
			$y = $this->getRandomY($this->width - $sy);
			$this->addToOccupied($x, $y, $sx, $sy);
			$tilt = rand(95, 100) / 100;
			$this->pivot = $this->perspective($this->pivot, $tilt);
			$stamp = $this->pivot;
			$stamp = imagerotate($stamp, $deg[$i], $white);
			imagecopy(
				$this->image,
				$stamp,
				$x,
				$y,
				0,
				0,
				imagesx($stamp),
				imagesy($stamp));
		}
	}


	public function drawFigures()
	{
		foreach ($this->figures as $figure) {
			$figure->render($this->image);
			$this->rotate();
		}
	}

	public function rotate()
	{
		if ($this->rotateChance > 0 && rand(0, 100) <= $this->rotateChance) {
			$deg = rand(0, 360);
			$white = \imagecolorallocate($this->image, 255, 255, 255);
			$this->image = \imagerotate($this->image, $deg, $white);
		}
	}


	public function generateAndSave()
	{
		$dir = __DIR__ . '/../dataset/';
		$binaryStream = fopen('php://memory', 'r+');
		try {
			imagepng($this->image, $binaryStream);
			rewind($binaryStream);
			$stringData = stream_get_contents($binaryStream);
			$name = $dir . $this->width . 'x' . $this->height . hash('md5', $stringData) . '.png';
			imagealphablending($this->image, true);
			imagepng($this->image, $name);
		} finally {
			\fclose($binaryStream);
		}
	}

	private function getRandomSquare()
	{
		$x = $this->getRandomX();
		$y = $this->getRandomY();
		$width = rand(20, 50);
		$filled = rand(0, 1);
		$figure = new Square($x, $y, $width, '#000', $filled);
		$this->addToOccupied($x, $y, $width, $width);
		return $figure;
	}

	private function getRandomTriangle()
	{
		$x = $this->getRandomX();
		$y = $this->getRandomY();
		$length = rand(20, 50);
		$filled = rand(0,1);
		$figure = new RightTriangle($x, $y, $length, '#000', $filled);
		$this->addToOccupied($x, $y, $length, $length);
		return $figure;
	}

	private function allocateCollor($image, $color)
	{
		if (is_string($color)) {
			$color = HexToRGB::translate($color);
		}
		return \imagecolorallocate($image, $color[0], $color[1], $color[2]);
	}

	private function addToOccupied($x, $y, $width, $height)
	{
		for ($i = 0; $i < $width; $i++) {
			$this->occupied['x'][] = $x + $i;
		}
		for ($i = 0; $i < $height; $i++) {
			$this->occupied['y'][] = $y + $i;
		}
	}

	private function getRandomX($width = 0)
	{
		return $this->getRandomCoordinate($width === 0 ? $this->width : $width, 'x');
	}

	private function getRandomY($height = 0)
	{
		return $this->getRandomCoordinate($height === 0 ? $this->height : $height, 'y');
	}

	private function getRandomCoordinate($limit = 0, $coordinateType)
	{
		$coord = rand(0, $limit);
		if (in_array($coord, $this->occupied[$coordinateType]) || in_array($coord + $limit, $this->occupied[$coordinateType])) {
			return $this->getRandomCoordinate($limit, $coordinateType);
		}
		return $coord;
	}

	function perspective($i,$gradient=0.85,$rightdown=TOP,$background=0xFFFFFF, $alpha=0) {
		$w=imagesx($i);
		$h=imagesy($i);
		$col=imagecolorallocatealpha($i,($background>>16)&0xFF,($background>>8)&0xFF,$background&0xFF,$alpha);

		$mult=5;
		$li=imagecreatetruecolor($w*$mult,$h*$mult);
		imagealphablending($li,false);
		imagefilledrectangle($li,0,0,$w*$mult,$h*$mult,$col);
		imagesavealpha($li,true);

		imagecopyresized($li,$i,0,0,0,0,$w*$mult,$h*$mult,$w,$h);
		imagedestroy($i);
		$w*=$mult;
		$h*=$mult;


		$image=imagecreatetruecolor($w,$h);
		imagealphablending($image,false);
		imagefilledrectangle($image,0,0,$w,$h,$col);
		imagealphablending($image,true);

		imageantialias($image,true);
		$test=$h*$gradient;

		$rdmod=$rightdown%2;
		$min=1;
		if($rightdown<2){
			for($y=0;$y<$h;$y++){
				$ny=$rdmod? $y : $h-$y;
				$off=round((1-$gradient)*$w*($ny/$h));
				$t=((1-pow(1-pow(($ny/$h),2),0.5))*(1-$gradient)+($ny/$h)*$gradient);
				$nt=$rdmod? $t : 1-$t;
				if(abs(0.5-$nt)<$min){
					$min=abs(0.5-$nt);
					$naty=$off;
				}
				imagecopyresampled($image,$li,
									round($off/2),$y,
									0,abs($nt*$h),
									$w-$off,1,
									$w,1);
			}
		} else {
			for($x=0;$x<$w;$x++){
				$nx=$rdmod? $x : $w-$x;
				$off=round((1-$gradient)*$h*($nx/$w));
				$t=((1-pow(1-pow(($nx/$w),2),0.5))*(1-$gradient)+($nx/$w)*$gradient);
				$nt=$rdmod? $t : 1-$t;
				if(abs(0.5-$nt)<$min){
					$min=abs(0.5-$nt);
					$natx=$off;
				}
				imagecopyresampled($image,$li,
									$x,round($off/2),
									abs($nt*$w),0,
									1,$h-$off,
									1,$h);
			}
		}
		imagedestroy($li);

		imageantialias($image,false);
		imagealphablending($image,false);
		imagesavealpha($image,true);

		$i=imagecreatetruecolor(($w+$naty)/$mult,($h+$natx)/$mult);
		imagealphablending($i,false);
		imagefilledrectangle($i,0,0,($w+$naty)/$mult,($h+$natx)/$mult,$col);
		imagealphablending($i,true);
		imageantialias($i,true);
		imagecopyresampled($i,$image,0,0,0,0,($w+$naty)/$mult,($h+$natx)/$mult,$w,$h);
		imagedestroy($image);
		imagealphablending($i,false);
		imageantialias($i,false);
		imagesavealpha($i,true);
		return $i;
	}
}