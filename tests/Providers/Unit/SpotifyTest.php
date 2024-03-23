<?php
/**
 * Class SpotifyTest
 *
 * @created      06.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Spotify;

/**
 * @property \chillerlan\OAuth\Providers\Spotify $provider
 */
final class SpotifyTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Spotify::class;
	}

}
