<?php
/**
 * Class SpotifyNewReleases
 *
 * @created      28.08.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;

/**
 *
 */
class SpotifyNewReleases extends SpotifyClient{

	protected array $newAlbums = [];

	/**
	 * the script runner
	 */
	public function getNewReleases(
		int    $since,
		int    $until,
		int    $minTracks,
		bool   $skipVariousArtist,
		bool   $skipAppearsOn,
		bool   $fromCache,
		string $cacheDir = __DIR__,
	):void{
		$loaded = $fromCache && $this->loadFromFile(['artists', 'albums'], $cacheDir);

		if(!$loaded){
			$this->getFollowedArtists();
			$this->getArtistReleases();
			$this->saveToFile(['artists', 'albums'], $cacheDir);
		}

		$this->filterReleases($since, $until, $minTracks, $skipVariousArtist, $skipAppearsOn);
		$this->getNewAlbumTracks($since, $until);
	}

	/**
	 * filters the releases for the followed artists and dumps the release info to the console
	 */
	public function filterReleases(int $since, int $until, int $minTracks, bool $skipVariousArtist, bool $skipAppearsOn):void{
		$this->newAlbums = [];
		$releaseinfo     = [];

		foreach($this->albums as $albums){

			foreach($albums as $album){

				// skip if the release has fewer than the minimum tracks
				if($album->total_tracks < $minTracks){
					continue;
				}

				// skip the "Various Artists" samplers
				if(
					$skipVariousArtist
					&& !empty($album->artists)
					&& strtolower($album->artists[0]->name) === 'various artists'
				){
					continue;
				}

				// skip "appears on" releases
				if($skipAppearsOn && $album->album_group === 'appears_on'){
					continue;
				}

				$releaseDate = match($album->release_date_precision){
					'month' => $album->release_date.'-01',
					'year'  => $album->release_date.'-01-01',
					default => $album->release_date,
				};

				$rdate = strtotime($releaseDate);

				// skip if the release is outside the date range
				if($rdate < $since || $rdate > $until){
					continue;
				}

				$this->newAlbums[$album->id] = $album->id;
				$releaseinfo[$releaseDate][] = $album;
			}

		}

		// sort the $releaseinfo array by release date (descending)
		krsort($releaseinfo);

		// dump the new release info to console
		foreach($releaseinfo as $date => $releases){
			[$year, $month, $day] = explode('-', $date);

			$this->logger->info('');
			$this->logger->info(date('--- l, jS F Y\: ---', mktime(0, 0, 0, (int)$month, (int)$day, (int)$year)));
			$this->logger->info('');

			foreach($releases as $k => $release){
				$this->logger->info('['.(++$k).'] '.implode(', ', array_column($release->artists, 'name')).' - '.$release->name);
			}

			$this->logger->info('');
		}

	}

	/**
	 * fetches the tracks for the filtered releases and puts the first of each album into a playlist
	 */
	protected function getNewAlbumTracks(int $since, int $until):void{
		$newtracks = [];

		// fetch the album tracks (why aren't the tracks in the albums response???)
		foreach(array_chunk(array_values($this->newAlbums), 20, true) as $chunk){ // API max = 20 albums
			$albums = $this->request('/v1/albums', ['ids' => implode(',', $chunk), 'market' => $this->market]);
			$data   = MessageUtil::decodeJSON($albums);

			if(!isset($data->albums)){
				$this->logger->warning('invalid albums response');

				continue;
			}

			foreach($data->albums as $album){
				$tracks = array_column($album->tracks->items, 'id');
				$id     = array_shift($tracks);

				$newtracks[$id] = $id;
			}

			usleep(self::sleepTimer);
		}

		$playlistID = $this->createPlaylist(
			sprintf('new releases %s - %s', date('d.m.Y', $since), date('d.m.Y', $until)),
			sprintf('new releases by the artists i\'m following, %s - %s', date('d.m.Y', $since), date('d.m.Y', $until))
		);

		$this->addTracks($playlistID, $newtracks);
	}

}
