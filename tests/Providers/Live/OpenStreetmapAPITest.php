<?php
/**
 * Class OpenStreetmapAPITest
 *
 * @created      12.05.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\OpenStreetmap;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap $provider
 */
#[Group('providerLiveTest')]
class OpenStreetmapAPITest extends OAuth1ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return OpenStreetmap::class;
	}

	protected function getEnvPrefix():string{
		return 'OPENSTREETMAP';
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, $user->displayName);
	}

}
