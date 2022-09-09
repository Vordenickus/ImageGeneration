<?php

namespace Volochaev\ImageGeneration\QR;

class QrGenerator
{

	public function __construct()
	{

	}


	protected function getRandomHash($algo = 'md5')
	{
		$size = rand(128, 512);
		$rBytes = random_bytes($size);

		return hash($algo, $rBytes);
	}

	public function createQr()
	{
		$hash = $this->getRandomHash();
		$qr = new Qr($hash);

		return $qr;
	}


	public function insertQr()
	{

	}
}
