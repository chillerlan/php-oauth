<?php
/**
 * Class YouTubeTest
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\YouTube;

/**
 * @property \chillerlan\OAuth\Providers\YouTube $provider
 */
final class YouTubeTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return YouTube::class;
	}

}
