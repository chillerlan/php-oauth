<?php
/**
 * Spotify new releases - proof of concept (The Friday-Script)
 *
 * Crawls the releases of the artists the user follows and looks for new releases for a given date range.
 *
 * @link https://twitter.com/codemasher/status/974755990053834752
 *
 * @created      16.03.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

/**
 * @var \OAuthProviderFactory $factory
 * @var \chillerlan\OAuth\Providers\Spotify $spotify
 * @var string $ENVVAR
 */

require_once __DIR__.'/spotify-common.php';
require_once __DIR__.'/SpotifyNewReleases.php';

$since             = strtotime('last Saturday'); // (time() - 7 * 86400); // last week
$until             = time();                     // adjust to your likes
$minTracks         = 1;                          // minimum number of tracks per album (1 = single releases)
$skipAppearsOn     = true;
$skipVariousArtist = true;
$fromCache         = false;

$spotify = $factory->getProvider(SpotifyNewReleases::class, $ENVVAR);
$spotify->getNewReleases($since, $until, $minTracks, $skipVariousArtist, $skipAppearsOn, $fromCache);

/*
// crawl for yearly album releases in the given range
foreach(range(1970, 1979) as $year){
	$since = \mktime(0, 0, 0, 1, 1, $year);
	$until = \mktime(23, 59, 59, 12, 31, $year);

	$client->getNewReleases($since, $until, 5, false, true, true);
}
*/

exit;
