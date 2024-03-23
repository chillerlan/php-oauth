<?php
/**
 * Class SpotifyClient
 *
 * @created      28.08.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Spotify;

/**
 *
 */
class SpotifyClient extends Spotify{

	protected const sleepTimer = 250000; // sleep between requests (µs)

	protected object $me;
	protected string $id;
	protected string $market;
	protected array  $artists = [];
	protected array  $albums = [];

	protected function construct():void{
		// set the servicename to the original provider's name so that we use the same tokens
		$this->serviceName = 'Spotify';
		$this->getMe();
	}

	/**
	 * @param string[] $vars
	 */
	protected function saveToFile(array $vars, string $dir):void{

		foreach($vars as $var){
			file_put_contents(
				sprintf('%s/%s.json', rtrim($dir, '\\/'), $var),
				json_encode($this->{$var}, (JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE))
			);
		}

	}

	/**
	 * @param string[] $vars
	 */
	protected function loadFromFile(array $vars, string $dir):bool{

		foreach($vars as $var){
			$file = sprintf('%s/%s.json', rtrim($dir, '\\/'), $var);

			if(!file_exists($file)){
				return false;
			}


			$data = json_decode(file_get_contents($file));

			foreach($data as $k => $v){
				$this->{$var}[$k] = $v;
			}

		}

		return true;
	}

	/**
	 * fetch the currently authenticated user
	 */
	protected function getMe():void{
		$me = $this->me();

		if($me->getStatusCode() !== 200){
			throw new RuntimeException('could not fetch data from /me endpoint');
		}

		$json = MessageUtil::decodeJSON($me);

		if($json === false || !isset($json->country, $json->id)){
			throw new RuntimeException('invalid response from /me endpoint');
		}

		$this->me     = $json;
		$this->id     = $this->me->id;
		$this->market = $this->me->country;
	}

	/**
	 * fetch the artists the user is following
	 */
	public function getFollowedArtists():array{
		$this->artists = [];

		$params = [
			'type'  => 'artist',
			'limit' => 50, // API max = 50 artists
			'after' => null,
		];

		do{
			$meFollowing = $this->request('/v1/me/following', $params);
			$data        = MessageUtil::decodeJSON($meFollowing);

			if($meFollowing->getStatusCode() === 200){

				foreach($data->artists->items as $artist){
					$this->artists[$artist->id] = $artist;

					$this->logger->info('artist: '.$artist->name);
				}

				$params['after'] = ($data->artists->cursors->after ?? '');

				$this->logger->info(sprintf('next cursor: %s', $params['after']));
			}
			// not dealing with this
			else{

				if(isset($data->error)){
					$this->logger->error($data->error->message.' ('.$data->error->status.')');
				}

				break;
			}

			usleep(self::sleepTimer);
		}
		while($params['after'] !== '');

		$this->logger->info(sprintf('fetched %s artists', count($this->artists)));

		return $this->artists;
	}

	/**
	 * fetch the releases for the followed artists
	 */
	public function getArtistReleases():array{
		$this->albums = [];

		foreach($this->artists as $artistID => $artist){
			// WTB bulk endpoint /artists/albums?ids=artist_id1,artist_id2,...
			$artistAlbums = $this->request(sprintf('/v1/artists/%s/albums', $artistID), ['market' => $this->market]);

			if($artistAlbums->getStatusCode() !== 200){
				$this->logger->warning(sprintf('could not fetch albums for artist "%s"', $artist->name));

				continue;
			}

			$data = MessageUtil::decodeJSON($artistAlbums);

			if(!isset($data->items)){
				$this->logger->warning(sprintf('albums response empty for artist "%s"', $artist->name));

				continue;
			}

			foreach($data->items as $album){
				$this->albums[$artistID][$album->id] = $album;

				$this->logger->info(sprintf('album: %s - %s', $artist->name, $album->name));

			}

			usleep(self::sleepTimer);
		}

		return $this->albums;
	}

	/**
	 * get the tracks from the given playlist
	 */
	public function getPlaylist(string $playlistID):array{

		$params = [
			'fields' => 'total,limit,offset,items(track(id,name,album(id,name),artists(id,name)))',
			'market' => $this->market,
			'offset' => 0,
			'limit'  => 100,
		];

		$playlist = [];
		$retry  = 0;

		do{
			$response = $this->request(sprintf('/v1/playlists/%s/tracks', $playlistID), $params);

			if($retry > 3){
				throw new RuntimeException('error while retrieving playlist');
			}

			if($response->getStatusCode() !== 200){
				$this->logger->warning(sprintf('playlist endpoint http/%s', $response->getStatusCode()));

				$retry++;

				continue;
			}

			$json = MessageUtil::decodeJSON($response);

			if(!isset($json->items)){
				$this->logger->warning('empty playlist response');

				$retry++;

				continue;
			}

			foreach($json->items as $item){
				$playlist[$item->track->id] = $item->track;
			}

			$params['offset'] += 100;
			$retry             = 0;

		}
		while($params['offset'] <= $json->total);

		return $playlist;
	}

	/**
	 * create a new playlist
	 */
	public function createPlaylist(string $name, string $description):string{

		$createPlaylist = $this->request(
			path   : sprintf('/v1/users/%s/playlists', $this->id),
			method : 'POST',
			body   : [
				'name'          => $name,
				'description'   => $description,
				// we'll never create public playlists - that's up to the user to decide
				'public'        => false,
				'collaborative' => false,
			],
			headers: ['Content-Type' => 'application/json'],
		);

		if($createPlaylist->getStatusCode() !== 201){
			throw new RuntimeException('could not create a new playlist');
		}

		$playlist = MessageUtil::decodeJSON($createPlaylist);

		if(!isset($playlist->id)){
			throw new RuntimeException('invalid create playlist response');
		}

		$this->logger->info(sprintf('created playlist: "%s" ("%s")', $name, $description));
		$this->logger->info(sprintf('spotify:user:%s:playlist:%s', $this->id, $playlist->id));
		$this->logger->info(sprintf('https://open.spotify.com/playlist/%s', $playlist->id));

		return $playlist->id;
	}

	/**
	 * add the tracks to the given playlist
	 */
	public function addTracks(string $playlistID, array $trackIDs):static{

		$uris = array_chunk(
			array_map(fn(string $t):string => 'spotify:track:'.$t , array_values($trackIDs)), // why not just ids???
			100 // API max = 100 track URIs
		);

		foreach($uris as $i => $chunk){

			$playlistAddTracks = $this->request(
				path   : sprintf('/v1/playlists/%s/tracks', $playlistID),
				method : 'POST',
				body   : ['uris' => $chunk],
				headers: ['Content-Type' => 'application/json'],
			);

			usleep(self::sleepTimer);

			if($playlistAddTracks->getStatusCode() === 201){
				$json = MessageUtil::decodeJSON($playlistAddTracks);

				$this->logger->info(sprintf('added tracks %s/%s [%s]', ++$i, count($uris), $json->snapshot_id));

				continue;
			}

			$this->logger->warning(sprintf('error adding tracks: http/%s', $playlistAddTracks->getStatusCode())); // idc
		}

		return $this;
	}

}
