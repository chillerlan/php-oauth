<?php
/**
 * @see          https://github.com/izayl/spotify-box
 *
 * @created      09.01.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;

/**
 * invoke the spotify client first
 *
 * @var \chillerlan\OAuth\Providers\Spotify $spotify
 */
require_once __DIR__.'/../Spotify/spotify-common.php';

/**
 * @var \OAuthProviderFactory               $factory
 * @var \chillerlan\OAuth\Providers\GitHub  $github
 */

require_once __DIR__.'/github-common.php';

$logger      = $factory->getLogger();

$gistID      = null; // set to null to create a new gist
$gistname    = '🎵 My Spotify Top Tracks';
$description = 'auto generated spotify track list';
$public      = false;

// fetch top tracks
$tracks = $spotify->request(path: '/v1/me/top/tracks', params: ['time_range' => 'short_term']);
#$tracks = $spotify->request(path: '/v1/me/player/recently-played');

if($tracks->getStatusCode() !== 200){
	throw new RuntimeException('could not fetch spotify top tracks');
}

$json = MessageUtil::decodeJSON($tracks);
// the JSON body for the gist
$body = [
	'description' => $description,
	'public'      => $public,
	'files'       => [
		$gistname       => ['filename' => $gistname, 'content' => ''],
		$gistname.'.md' => ['filename' => $gistname.'.md', 'content' => ''],
	],
];

// create the file content
foreach($json->items as $track){
	$t = ($track->track ?? $track); // recent tracks or top tracks object

	// plain text
	$body['files'][$gistname]['content'] .= sprintf(
		"%s - %s\n",
		($t->artists[0]->name ?? ''),
		($t->name ?? ''),
	);

	// markdown
	$body['files'][$gistname.'.md']['content'] .= sprintf(
		"1. [%s](%s) - [%s](%s)\n", // the "1." will create an ordered list starting at 1
		($t->artists[0]->name ?? ''),
		($t->artists[0]->external_urls->spotify ?? ''),
		($t->name ?? ''),
		($t->external_urls->spotify ?? '')
	);
}

// create/update the gist
$path   = '/gists';
$method = 'POST';

if($gistID !== null){
	$path   .= '/'.$gistID;
	$method = 'PATCH';
}

$response = $github->request(path: $path, method: $method, body: $body, headers: ['content-type' => 'application/json']);

if($response->getStatusCode() === 201){
	$json = MessageUtil::decodeJSON($response);

	$logger->info(sprintf('created gist https://gist.github.com/%s', $json->id));
}
elseif($response->getStatusCode() === 200){
	$logger->info(sprintf('updated gist https://gist.github.com/%s', $gistID));
}
else{
	throw new RuntimeException(sprintf("error while creating/updating gist: \n\n%s", MessageUtil::toString($response)));
}

exit;
