<?php
/**
 * search-tracks.php
 *
 * @see https://de.wikipedia.org/wiki/Hr3_Clubnight
 * @see https://www.fr.de/kultur/letzten-rille-11671177.html
 *
 * @created      26.03.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

/**
 * @var \OAuthExampleProviderFactory        $factory
 * @var \chillerlan\OAuth\Providers\Spotify $spotify
 */
require_once __DIR__.'/spotify-common.php';
require_once __DIR__.'/MixesDBTrackSearch.php';

$file           = __DIR__.'/cache/clubnights.json';
$since          = strtotime('1990-05-05'); // first clubnight: 1990-05-05
$until          = strtotime('2000-01-01'); // last clubnight: 2014-06-07 (studio), 2014-06-14 (live)
$find           = ['Dag', 'Fenslau', 'Pascal' /* F.E.O.S. */, 'Talla', 'Taucher', 'Tom Wax', 'Ulli Brenner', 'Väth'];
$limit          = 1;
$playlistPerSet = false;

if(!file_exists($file)){
	include __DIR__.'/mixesdb-scrape.php';
}

$spotify = $factory->getProvider(MixesDBTrackSearch::class, OAuthExampleProviderFactory::STORAGE_FILE);
$spotify->getTracks($file, $since, $until, $find, $limit, $playlistPerSet);

exit;
