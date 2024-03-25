<?php
/**
 * Class FoursquareAPITest
 *
 * @created      10.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Foursquare;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Foursquare $provider
 */
#[Group('providerLiveTest')]
class FoursquareAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Foursquare::class;
	}

	protected function getEnvPrefix():string{
		return 'FOURSQUARE';
	}

}
