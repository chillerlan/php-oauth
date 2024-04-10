<?php
/**
 * Class NPROneAPITest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\NPROne;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\NPROne $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
final class NPROneAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return NPROne::class;
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, $user->email);
	}

}
