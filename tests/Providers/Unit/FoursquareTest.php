<?php
/**
 * Class FoursquareTest
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Foursquare;

/**
 * @property \chillerlan\OAuth\Providers\Foursquare $provider
 */
final class FoursquareTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Foursquare::class;
	}

}
