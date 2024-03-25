<?php
/**
 * Class OpenStreetmap2APITest
 *
 * @created      05.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\OpenStreetmap2;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap2 $provider
 */
#[Group('providerLiveTest')]
final class OpenStreetmap2APITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return OpenStreetmap2::class;
	}

	protected function getEnvPrefix():string{
		return 'OPENSTREETMAP2';
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, $user->displayName);
	}

}
