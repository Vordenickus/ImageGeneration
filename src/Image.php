<?php

namespace Volochaev\ImageGeneration;

use Volochaev\ImageGeneration\Figures\Circle;
use Volochaev\ImageGeneration\Figures\RightTriangle;
use Volochaev\ImageGeneration\Figures\Square;
use Volochaev\ImageGeneration\Figures\TextString;
use Volochaev\ImageGeneration\Helpers\HexToRGB;

class Image
{
	public $width;
	public $height;
	protected $background;
	protected $hexBackground;
	protected $image;
	protected $figures = [];
	protected $rotateChance = 40;
	protected $filterChance = 40;
	protected $label = '';
	protected $pivot;
	protected $occupied = ['x' => [], 'y' => []];


	public function __construct($width, $height, $background)
	{
		$this->width = $width;
		$this->height = $height;

		$this->image = imagecreatetruecolor($this->width, $this->height);
		$this->pivot = imagecreatefrompng(__DIR__ . '/../ideal/scan-mark.png');
		$this->background = $this->allocateCollor($this->image, $background);
		$this->hexBackground = $background;
		
		imagefill($this->image, 0, 0, $this->background);
	}


	public function addFigures()
	{
		$figures = [
			'circle',
			'rightTriangle',
			'square',
			'string'
		];
		$colors = [
			'#000000',
			'#f34044',
			'#734f34',
			'#fd3292',
			'#f09746'
		];
		$amountOfFigures = 0;
		if ($this->width === 512) {
			$amountOfFigures = \mt_rand(50,70);
		} elseif ($this->width === 768) {
			$amountOfFigures = \mt_rand(60, 100);
		} elseif ($this->width === 1024) {
			$amountOfFigures = \mt_rand(100, 120);
		}

		for ($i = 0; $i < $amountOfFigures; $i++) {
			$figure = mt_rand(0, count($figures) -1);
			$color = mt_rand(0, count($colors) - 1);
			$this->figures[] = $this->getRandomFigure($figures[$figure], $colors[$color]);
		}
	}


	public function addPivots()
	{
		$deg = [0, 90, 180, 270];
		for($i = 0; $i < 4; $i++) {
			$sx = imagesx($this->pivot);
			$sy = imagesy($this->pivot);
			$coord = $this->getRandomCoordinates(true);
			$x = $coord['x'];
			$y = $coord['y'];
			$tilt = mt_rand(95, 100) / 100;
			$this->pivot = $this->imageFilter($this->pivot);
			$this->pivot = $this->perspective($this->pivot, $tilt, $this->getRandomTiltSide(), hexdec($this->hexBackground));
			$stamp = $this->pivot;
			$stamp = imagerotate($stamp, $deg[$i], $this->background);
			$label = $this->calculateLabel($x, $y, imagesx($stamp), imagesx($stamp), $i);
			$this->label .= $label . PHP_EOL;
			$this->addToOccupied($x, $y, $sx, $sy);
			imagecopy(
				$this->image,
				$stamp,
				$x,
				$y,
				0,
				0,
				imagesx($stamp),
				imagesy($stamp)
			);
		}
	}


	protected function calculateLabel($x, $y, $width, $height, $classId)
	{
		$imageWidth = imagesx($this->image);
		$imageHeigth = imagesy($this->image);
		$xMin = ($x + $width / 2) / $imageWidth;
		$yMin = ($y + $height / 2) / $imageHeigth;
		$xMax = $width / $imageWidth;
		$yMax = $height / $imageHeigth;
		return "$classId $xMin $yMin $xMax $yMax";
	}


	public function drawFigures()
	{
		foreach ($this->figures as $figure) {
			$figure->render($this->image);
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
			$name = $dir . $this->width . 'x' . $this->height . '_' . hash('md5', $stringData);
			$imageName = $name . '.png';
			$labelName = $name . '.txt';
			imagealphablending($this->image, true);
			imagesavealpha($this->image, true);
			imagepng($this->image, $imageName);
			$labelStream = fopen($labelName, 'w+');
			try {
				fwrite($labelStream, $this->label);
			} finally {
				fclose($labelStream);
			}
		} finally {
			fclose($binaryStream);
			imagedestroy($this->image);
			imagedestroy($this->pivot);
		}
	}


	protected function getRandomFigure($figure, $color)
	{
		$randomFigure = null;
		switch ($figure) {
			case 'square':
				$randomFigure = $this->getRandomSquare($color);
				break;
			case 'circle':
				$randomFigure = $this->getRandomCircle($color);
				break;
			case 'rightTriangle':
				$randomFigure = $this->getRandomTriangle($color);
				break;
			case 'string':
				$randomFigure = $this->getRandomString($color);
				break;
		}
		if (mt_rand(0, 100) < $this->rotateChance) {
			$deg = mt_rand(30, 80);
			$randomFigure->rotate($deg);
		}

		return $randomFigure;
	}


	protected function getRandomSquare($color)
	{
		$width = mt_rand(20, 50);
		$coordinates = $this->getRandomCoordinates();
		$filled = mt_rand(0, 1);
		$figure = new Square(
			$coordinates['x'],
			$coordinates['y'],
			$width,
			$color,
			$filled
		);
		return $figure;
	}


	protected function getRandomTriangle($color)
	{
		$length = mt_rand(20, 50);
		$coordinates = $this->getRandomCoordinates();
		$filled = mt_rand(0,1);
		$figure = new RightTriangle($coordinates['x'], $coordinates['y'], $length, $color, $filled);
		return $figure;
	}


	protected function getRandomCircle($color)
	{
		$length = mt_rand(20, 50);
		$coordinates = $this->getRandomCoordinates();
		$filled = mt_rand(0,1);
		$figure = new Circle($coordinates['x'], $coordinates['y'], $length, $color, $filled);
		return $figure;
	}


	protected function getRandomString($color)
	{
		$coordinates = $this->getRandomCoordinates();
		$figure = new TextString($coordinates['x'], $coordinates['y'], $color);
		return $figure;
	}


	protected function allocateCollor($image, $color)
	{
		if (is_string($color)) {
			$color = HexToRGB::translate($color);
		}
		return imagecolorallocate($image, $color[0], $color[1], $color[2]);
	}


	protected function getRandomTiltSide()
	{
		return mt_rand(0,3);
	}


	protected function getRandomCoordinates($unique = false) {
		$x = $this->getRandomX();
		$y = $this->getRandomY();
		if ($unique && in_array($x, $this->occupied['x']) && \in_array($y, $this->occupied['y'])) {
			return $this->getRandomCoordinates($unique);
		}
		return ['x' => $x,'y' => $y];
	}


	protected function getRandomX($width = 0)
	{
		return $this->getRandomCoordinate($width === 0 ? $this->width : $width, 'x');
	}


	protected function getRandomY($height = 0)
	{
		return $this->getRandomCoordinate($height === 0 ? $this->height : $height, 'y');
	}


	protected function imageFilter($img)
	{
		if (mt_rand(0, 100) < $this->filterChance) {
			imagefilter($img, IMG_FILTER_BRIGHTNESS, mt_rand(-100, 100));
		}
		if (mt_rand(0, 100) < $this->filterChance) {
			imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
		}
		return $img;
	}


	protected function getRandomCoordinate($limit = 0)
	{
		$coord = mt_rand(0, $limit);
		return $coord;
	}


	protected function addToOccupied($x, $y, $width, $height)
	{
		for ($i = 0; $i < $width; $i++) {
			$deltaPlus = $x + $i;
			$deltaMinus = $x - $i;
			$this->occupied['x'][] = $deltaPlus;
			if ($deltaMinus !== $deltaPlus) {
				$this->occupied['x'][] = $deltaMinus;
			}
		}

		for ($i = 0; $i < $height; $i++) {
			$deltaPlus = $y + $i;
			$deltaMinus = $y - $i;
			$this->occupied['x'][0] = $deltaPlus;
			if ($deltaMinus !== $deltaPlus) {
				$this->occupied['x'][] = $deltaMinus;
			}
		}
	}


	/**
	 * Сколлективизиравано
	 */
	protected function perspective($i,$gradient=0.85,$rightdown=0,$background=0xFFFFFF, $alpha=0) {
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
