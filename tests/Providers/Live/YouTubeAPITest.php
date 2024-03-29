<?php
/**
 * Class GoogleAPITest
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\AuthenticatedUser;
use chillerlan\OAuth\Providers\YouTube;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Google $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
final class YouTubeAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return YouTube::class;
	}

	protected function getEnvPrefix():string{
		return 'GOOGLE';
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertSame($this->TEST_USER, $user->email);
	}

}
