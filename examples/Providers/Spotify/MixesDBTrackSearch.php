<?php
/**
 * Class MixesDBTrackSearch
 *
 * @created      30.08.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\HTTP\Utils\MessageUtil;

/**
 *
 */
class MixesDBTrackSearch extends SpotifyClient{

	/**
	 * search tracks on spotify from the given mixesdb track lists
	 */
	public function getTracks(
		string $clubnightsJSON,
		int    $since,
		int    $until,
		array  $find = [],
		int    $limit = 5,
		bool   $playlistPerSet = false,
	):void{
		$clubnights = json_decode(file_get_contents($clubnightsJSON), true);
		$tracks     = [];

		foreach($clubnights as $date => $sets){
			$date = strtotime($date);
			// skip by date
			if($date < $since || $date > $until){
				continue;
			}

			foreach($sets as $name => $set){
				// skip by inclusion list
				if($this->setContains($name, $find)){
					continue;
				}

				$this->logger->info($name);
				$setTracks = [];

				foreach($set as $track){
					$track = $this->cleanTrack($track);

					if(empty($track)){
						continue;
					}

					$this->logger->info(sprintf('search: %s', $track));

					$response = $this->request('/v1/search', [
						'q'      => $this->getSearchTerm($track),
						'type'   => 'track',
						'limit'  => $limit,
						'market' => $this->market,
					]);

					usleep(self::sleepTimer);

					if($response->getStatusCode() !== 200){
						continue;
					}

					$data = MessageUtil::decodeJSON($response);

					foreach($data->tracks->items as $i => $item){
						$setTracks[$item->id] = $item->id;

						$this->logger->info(sprintf('found: [%s][%s] %s - %s', ++$i, $item->id, implode(', ', array_column($item->artists, 'name')), $item->name));
					}

				}

				if($playlistPerSet){
					$playlistID = $this->createPlaylist($name, '');
					$this->addTracks($playlistID, $setTracks);
				}

				$tracks = array_merge($tracks, $setTracks);
			}

		}

		if(!$playlistPerSet){
			$playlistID = $this->createPlaylist('mixesdb search result', implode(', ', $find));
			$this->addTracks($playlistID, $tracks);
		}

	}

	/**
	 * check a string for the occurence of any in the given array of needles
	 */
	protected function setContains(string $haystack, array $needles):bool{
		$haystack = mb_strtolower($haystack);

		return !empty($needles) && str_replace(array_map('mb_strtolower', $needles), '', $haystack) === $haystack;
	}

	/**
	 * clean any unwanted symbols/strings from the track name
	 */
	protected function cleanTrack(string $track):string{
		// strip time codes [01:23] and record IDs [EYE Q - 001] from name
		return trim(preg_replace(['/^\[[\d:?]+\] /', '/ \[[^]]+\]/'], '', $track), ' -?');
	}

	/**
	 * prepare the spotify search term
	 */
	protected function getSearchTerm(string $track):string{
		$at = explode(' - ', $track, 2); // artist - track

		return match (count($at)){
			1 => sprintf('artist:%1$s track:%1$s', $at[0]),
			2 => sprintf('artist:%s track:%s', $at[0], $at[1]),
		};
	}

}
