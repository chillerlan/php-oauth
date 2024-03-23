<?php
/**
 * Class SoundCloudTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\SoundCloud;

/**
 * @property \chillerlan\OAuth\Providers\SoundCloud $provider
 */
final class SoundCloudTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return SoundCloud::class;
	}

}
