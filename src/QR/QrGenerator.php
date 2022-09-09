<?php

namespace Volochaev\ImageGeneration\QR;

class QrGenerator
{
	public function __construct()
	{
	}


	public function createQr($randomScale = false)
	{
		$hash = $this->getRandomHash();
		$qr = new Qr($hash, $randomScale);

		return $qr;
	}


	protected function getRandomHash($algo = 'md5')
	{
		$size = rand(128, 512);
		$rBytes = random_bytes($size);

		return hash($algo, $rBytes);
	}
}
