<?php
/**
 * topalbum-patchwork.php
 *
 * @link         https://www.reddit.com/r/lastfm/search?q=flair_name%3A%22Chart%22&restrict_sr=1
 * @link         https://github.com/Dinduks/Lastfm-Top-Albums
 *
 * @created      03.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\HTTP\Utils\MessageUtil;

/**
 * @var \chillerlan\OAuth\Providers\LastFM $lfm
 */

require_once __DIR__.'/lastfm-common.php';

$urlcache  = './urlcache'; // downloaded album covers
$imgcache  = './cache';    // generated patchworks

try{
	$request = json_decode(file_get_contents('php://input'));

	if(!$request || !isset($request->username)){
		header('HTTP/1.1 400 Bad Request');
		sendResponse(['error' => 'invalid request']);
	}

	$user      = trim($request->username);
	$rows      = max(0, min(intval($request->height), 10));
	$cols      = max(0, min(intval($request->width), 10));
	$imageSize = max(30, min(intval($request->imagesize), 150));
	$period    = trim($request->period);
	$limit     = ($rows * $cols + 10);

	// doesn't necessarily need session auth, api key alone is sufficient
	$response = $lfm->request('user.getTopAlbums', ['user' => $user, 'period' => $period, 'limit' => $limit]);

	if($response->getStatusCode() !== 200){
		header('HTTP/1.1 '.$response->getStatusCode().' '.$response->getReasonPhrase());
		sendResponse(['error' => 'last.fm error']);
	}

	$json = MessageUtil::decodeJSON($response);

	if(!$json || !isset($json->topalbums->album)){
		header('HTTP/1.1 500 Internal Server Error');
		sendResponse(['error' => '...']);
	}

	// a not-too-unique hash
	$hash = sha1(json_encode([$rows, $cols, $imageSize,
		array_column($json->topalbums->album, 'artist'),
		array_column($json->topalbums->album, 'name'),
		array_column($json->topalbums->album, 'mbid'),
	]));

	$imagefile = $imgcache.'/'.$hash.'.jpg';

	if(file_exists($imagefile)){
		header('HTTP/1.1 200 OK');
		sendResponse(['image' => '<img src="'.$imagefile.'"/>', 'cached' => true]);
	}

	$res = [];

	foreach(array_column($json->topalbums->album, 'image') as $img){

		if(empty($img)){
			continue;
		}

		try{
			$path = getImage($img[(count($img) - 1)]->{'#text'}, $urlcache);
			$ext  = pathinfo($path, PATHINFO_EXTENSION);

			$res[] = match($ext){
				'jpg' => imagecreatefromjpeg($path),
				'png' => imagecreatefrompng($path),
				'gif' => imagecreatefromgif($path),
			};
		}
		catch(Throwable){
			continue;
		}
	}

	$patchwork = imagecreatetruecolor(($cols * $imageSize), ($rows * $imageSize));
	$bg        = imagecolorallocate($patchwork, 0, 0, 0);
	imagefill($patchwork, 0, 0, $bg);

	for($y = 0; $y < $rows; $y++){
		for($x = 0; $x < $cols; $x++){

			if(empty($res)){
				break;
			}

			$img = array_shift($res);
			imagecopyresampled($patchwork, $img, ($x * $imageSize), ($y * $imageSize), 0, 0, $imageSize, $imageSize, imagesx($img), imagesy($img));
			imagedestroy($img);
		}
	}

	// save the image into a file
	imagejpeg($patchwork, $imagefile, 85);
	imagedestroy($patchwork);

	if(file_exists($imagefile)){
		header('HTTP/1.1 200 OK');
		sendResponse(['image' => '<img src="'.$imagefile.'"/>', 'cached' => false]);
	}

}
// Pokémon exception handler
catch(Exception $e){
	header('HTTP/1.1 500 Internal Server Error');
	sendResponse(['error' => $e->getMessage()]);
}

exit;

function getImage(string $url, string $urlcache):string{
	$path = parse_url($url, PHP_URL_PATH);

	if(file_exists($urlcache.$path)){
		return $urlcache.$path;
	}

	$dir       = $urlcache.dirname($path);
	$imagedata = file_get_contents($url);

	if(!file_exists($dir)){
		mkdir($dir, 0777, true);
	}

	file_put_contents($urlcache.$path, $imagedata);

	return $urlcache.$path;
}

function sendResponse(array $response):void{
	header('Content-type: application/json;charset=utf-8;');

	echo json_encode($response, JSON_PRETTY_PRINT);
	exit;
}

