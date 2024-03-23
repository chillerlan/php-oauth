<?php
/**
 * Class OpenStreetmapTest
 *
 * @created      12.05.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\OpenStreetmap;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap $provider
 */
final class OpenStreetmapTest extends OAuth1ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return OpenStreetmap::class;
	}

}
