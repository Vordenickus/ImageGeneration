<?php

namespace Volochaev\ImageGeneration;

use Volochaev\ImageGeneration\Config\ConfigLoader;
use Volochaev\ImageGeneration\Figures\Angle;
use Volochaev\ImageGeneration\Figures\Circle;
use Volochaev\ImageGeneration\Figures\Poligon;
use Volochaev\ImageGeneration\Figures\RightTriangle;
use Volochaev\ImageGeneration\Figures\Square;
use Volochaev\ImageGeneration\Figures\TextString;
use Volochaev\ImageGeneration\Figures\Triangle;
use Volochaev\ImageGeneration\Helpers\HexToRGB;
use Volochaev\ImageGeneration\Helpers\LoadImage;
use Volochaev\ImageGeneration\Logging\Logger;
use Volochaev\ImageGeneration\QR\QrGenerator;

class Image
{
	public $width;
	public $height;

	private $logger;

	protected $qrGenerator;
	protected $background;
	protected $hexBackground;
	protected $image;
	protected $figures = [];
	protected $label = '';
	protected $pivot;
	protected $backgroundImage;
	protected $occupied = ['x' => [], 'y' => []];

	protected const FIGURES = [
		'circle',
		'rightTriangle',
		'square',
		'string',
		'triangle',
		'poligon',
		'angle'
	];
	protected const FIGURES_COLORS = [
		'#000000',
		'#f34044',
		'#734f34',
		'#fd3292',
		'#f09746'
	];

	protected $maxTilt;
	protected $rotateChance;
	protected $filterChance;


	public function __construct($width, $height, $background, $backgroundImage = '')
	{
		$this->width = $width;
		$this->height = $height;

		$this->maxTilt = ConfigLoader::cfg("MAX_TILT") ?? 100;
		$this->rotateChance = ConfigLoader::cfg("ROTATE_CHANCE") ?? 0;
		$this->filterChance = ConfigLoader::cfg("FILTER_CHANCE") ?? 0;

		$this->image = imagecreatetruecolor($this->width, $this->height);
		$this->pivot = imagecreatefrompng(__DIR__ . '/../ideal/scan-mark.png');
		$this->qrGenerator = new QrGenerator();
		if ($backgroundImage) {
			$image = LoadImage::loadImage($backgroundImage);
			if (!$image) {
				imagefill($this->image, 0, 0, $this->background);
			}
			$this->backgroundImage = $image;
			$this->applyBackgroundImage();
		} else {
			imagefill($this->image, 0, 0, $this->background);
		}
		if ($background) {
			$this->background = $this->allocateCollor($this->image, $background);
			$this->hexBackground = $background;
		}

		$this->logger = Logger::getInstance();
	}


	public function addFigures($amountOfFigures = -1)
	{
		$figures = static::FIGURES;
		$colors = static::FIGURES_COLORS;
		if ($this->width >= 416) {
			$amountOfFigures = $amountOfFigures == -1 ? rand(60,80) : $amountOfFigures;
		} elseif ($this->width >= 768 && $this->width < 864) {
			$amountOfFigures = $amountOfFigures == -1 ? rand(80, 120) : ceil($amountOfFigures * 1.4);
		} elseif ($this->width >= 864) {
			$amountOfFigures = $amountOfFigures == -1 ? rand(120, 150) : ceil($amountOfFigures * 1.8);
		}
		for ($i = 0; $i < $amountOfFigures; $i++) {
			$figure = rand(0, count($figures) -1);
			$color = rand(0, count($colors) - 1);
			$this->figures[] = $this->getRandomFigure($figures[$figure], $colors[$color]);
		}
	}


	public function addPivots()
	{
		$deg = [0, 90, 180, 270];
		for($i = 0; $i < 4; $i++) {
			$pivot =imagescale($this->pivot, rand(30, 60));
			$sx = imagesx($pivot);
			$sy = imagesy($pivot);
			$coord = $this->getRandomCoordinates(true, $sx);
			$x = $coord['x'];
			$y = $coord['y'];
			$tilt = rand($this->maxTilt, 100) / 100;
			$pivot = $this->imageFilter($pivot);
			$pivot = $this->perspective($pivot, $tilt, $this->getRandomTiltSide(), hexdec($this->hexBackground));
			$stamp = $pivot;
			$stamp = imagerotate($stamp, $deg[$i], $this->background);
			$label = $this->calculateLabel($x, $y, imagesx($stamp), imagesx($stamp), 0);
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
			imagedestroy($pivot);
		}
	}


	public function addQr($amount = -1)
	{
		if ($amount === -1) {
			$amount = rand(1, 3);
		}

		for ($i = 0; $i < $amount; $i++) {
			$randomScale = ConfigLoader::cfg('QR_SCALE');
			$qr = $this->qrGenerator->createQr($randomScale);
			if (rand(0, 100) < $this->rotateChance) {
				$deg = rand(0, 360);
				$qr->rotate($deg);
			}
			$qr = $qr->getImage();
			$tiltSide = $this->getRandomTiltSide();
			$tilt = rand($this->maxTilt, 100) / 100;
			$qr = $this->perspective($qr, $tilt, $tiltSide);
			$width = imagesx($qr);
			$height = imagesy($qr);
			$coordinates = $this->getRandomCoordinates(true, $width, $height);
			$x = $coordinates['x'];
			$y = $coordinates['y'];

			$this->addToOccupied($x, $y, $width, $width);

			$qr = $this->imageFilter($qr);

			imagecopy(
				$this->image,
				$qr,
				$x,
				$y,
				0,
				0,
				$width,
				$height
			);

			$label = $this->calculateLabel($x, $y, imagesx($qr), imagesy($qr), 1);
			$this->label .= $label . PHP_EOL;
			imagedestroy($qr);
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
			} catch(\Exception $ex) {
				$string = 'code: ' . $ex->getCode() . '; msg: ' . $ex->getMessage();
				$this->logger->error($string);
			} finally {
				fclose($labelStream);
			}
		} catch(\Exception $ex) {
			$string = 'code: ' . $ex->getCode() . '; msg: ' . $ex->getMessage();
			$this->logger->error($string);
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
				$randomFigure = $this->getRandomRightTriangle($color);
				break;
			case 'string':
				$randomFigure = $this->getRandomString($color);
				break;
			case 'triangle':
				$randomFigure = $this->getRandomTriangle($color);
				break;
			case 'poligon':
				$randomFigure = $this->getRandomPoligon($color);
				break;
			case 'angle':
				$randomFigure = $this->getRandomAngle($color);
				break;
		}
		if (rand(0, 100) < $this->rotateChance) {
			$deg = rand(30, 80);
			$randomFigure->rotate($deg);
		}

		return $randomFigure;
	}


	protected function getRandomSquare($color)
	{
		$width = rand(20, 50);
		$coordinates = $this->getRandomCoordinates();
		$filled = rand(0, 1);
		$figure = new Square(
			$coordinates['x'],
			$coordinates['y'],
			$width,
			$color,
			$filled
		);
		return $figure;
	}


	protected function getRandomRightTriangle($color)
	{
		$length = rand(20, 50);
		$coordinates = $this->getRandomCoordinates();
		$filled = rand(0,1);
		$figure = new RightTriangle($coordinates['x'], $coordinates['y'], $length, $color, $filled);
		return $figure;
	}

	protected function getRandomTriangle($color)
	{
		$width = rand(20,50);
		$coordinates = $this->getRandomCoordinates();
		$filled = rand(0,1);
		$figure = Triangle::constructInArea($coordinates['x'], $coordinates['y'], $width, $width, $filled, $color);
		return $figure;
	}


	protected function getRandomPoligon($color)
	{
		$width = rand(20,50);
		$coordinates = $this->getRandomCoordinates();
		$filled = rand(0,1);
		$amountOfVertices = rand(3,8);
		$figure = Poligon::createRandomPoligon(
			$this->image,
			$coordinates['x'],
			$coordinates['y'],
			$width,
			$width,
			$amountOfVertices,
			$filled,
			$color
		);
		return $figure;
	}


	protected function getRandomCircle($color)
	{
		$length = rand(20, 50);
		$coordinates = $this->getRandomCoordinates();
		$filled = rand(0,1);
		$figure = new Circle($coordinates['x'], $coordinates['y'], $length, $color, $filled);
		return $figure;
	}


	protected function getRandomString($color)
	{
		$coordinates = $this->getRandomCoordinates();
		$figure = new TextString($coordinates['x'], $coordinates['y'], $color);
		return $figure;
	}


	protected function getRandomAngle($color)
	{
		$width = rand(40, 60);
		$coordinates = $this->getRandomCoordinates();
		$figure = new Angle($coordinates['x'], $coordinates['y'], $width, $width, $color);
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
		return rand(0,3);
	}


	protected function getRandomCoordinates($unique = true, $width = 0, $height = 0) {
		$x = $this->getRandomX($width);
		$y = $this->getRandomY($height !== 0 ? $height : $width);
		if (
			$unique &&
			(
				(in_array($x, $this->occupied['x'])) &&
				(in_array($y, $this->occupied['y']))
			)) {
			return $this->getRandomCoordinates($unique);
		}
		return ['x' => $x,'y' => $y];
	}


	protected function getRandomX($width = 0)
	{
		$width = $width === 0 ? ($this->width) : ($this->width - $width);
		return $this->getRandomCoordinate($width);
	}


	protected function getRandomY($height = 0)
	{
		$height = $height === 0 ? ($this->height) : ($this->height - $height);
		return $this->getRandomCoordinate($height);
	}


	protected function imageFilter($img)
	{
		if (rand(0, 100) < $this->filterChance) {
			$tilt = rand($this->maxTilt, 100);
			$this->perspective($img, $tilt, $this->getRandomTiltSide(), hexdec($this->hexBackground));
		}
		if (rand(0, 100) < $this->filterChance) {
			imagefilter($img, IMG_FILTER_BRIGHTNESS, rand(-100, 100));
		}
		if (rand(0, 100) < $this->filterChance) {
			imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
		}
		return $img;
	}


	protected function getRandomCoordinate($limit = 0)
	{
		$coord = rand(0, $limit);
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
			$this->occupied['y'][0] = $deltaPlus;
			if ($deltaMinus !== $deltaPlus) {
				$this->occupied['y'][] = $deltaMinus;
			}
		}
	}


	protected function applyBackgroundImage()
	{
		$background = $this->backgroundImage;
		$image = $this->image;

		$background = imagescale($background, imagesx($image), imagesy($image));

		imagedestroy($this->backgroundImage);
		imagecopy($image, $background, 0, 0, 0, 0, imagesx($image), imagesy($image));
		imagedestroy($background);
	}


	/**
	 * ??????????????????????????????????????
	 * ?????????????????? ???????????? ??????????????????????
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
