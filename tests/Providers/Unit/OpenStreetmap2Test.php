<?php
/**
 * Class OpenStreetmapTest
 *
 * @created      05.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\OpenStreetmap2;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap $provider
 */
final class OpenStreetmap2Test extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return OpenStreetmap2::class;
	}

}
