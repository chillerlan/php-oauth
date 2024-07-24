<?php
/**
 * playlist-diff.php
 *
 * @created      31.08.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

require_once __DIR__.'/spotify-common.php';

class PlaylistDiff extends SpotifyClient{

	public function diff(string $playlistID1, string $playlistID2):array{
		$p1   = array_keys($this->getPlaylist($playlistID1));
		$p2   = array_keys($this->getPlaylist($playlistID2));
		$diff = array_diff($p1, $p2);

		$playlistID = $this->createPlaylist(
			'playlist diff',
			sprintf('diff between playlists "spotify:playlist:%s" and "spotify:playlist:%s"', $playlistID1, $playlistID2),
		);

		$this->addTracks($playlistID, $diff);

		return $diff;
	}

	public function merge(string $targetID, string ...$playlistIDs):array{
		$merged = $this->getPlaylist($targetID);

		foreach($playlistIDs as $playlistID){
			$merged = array_merge($merged, $this->getPlaylist($playlistID));
		}

		$playlistID = $this->createPlaylist(
			'playlist merge',
			sprintf('merged playlists "%s" into "spotify:playlist:%s"', implode('", "', $playlistIDs), $targetID),
		);

		$this->addTracks($playlistID, array_keys($merged));

		return $merged;
	}

}

/**
 * @var \OAuthExampleProviderFactory $factory
 * @var \PlaylistDiff                $spotify
 */

$spotify = $factory->getProvider(PlaylistDiff::class, OAuthExampleProviderFactory::STORAGE_FILE);
$spotify->diff('37i9dQZF1DX4UtSsGT1Sbe', '37i9dQZF1DXb57FjYWz00c');
$spotify->merge('37i9dQZF1DX4UtSsGT1Sbe', '37i9dQZF1DXb57FjYWz00c');
