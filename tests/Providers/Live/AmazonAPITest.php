<?php
/**
 * Class AmazonAPITest
 *
 * @created      10.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, UnauthorizedAccessException};
use chillerlan\OAuth\Providers\Amazon;
use chillerlan\OAuth\Storage\MemoryStorage;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Amazon $provider
 */
#[Group('providerLiveTest')]
final class AmazonAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Amazon::class;
	}

	protected function getEnvPrefix():string{
		return 'AMAZON';
	}

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertMatchesRegularExpression('/[a-z\d.]+/i', $user->id);
	}

	public function testUnauthorizedAccessException():void{
		$token                    = $this->storage->getAccessToken($this->provider->serviceName);
		// avoid refresh
		$token->expires           = AccessToken::EOL_NEVER_EXPIRES;
		$token->refreshToken      = null;
		// invalidate token
		$token->accessToken       = 'Atza|nope'; // amazon tokens are prefixed

		// using a temp storage here so that the local tokens won't be overwritten
		$tempStorage = (new MemoryStorage)->storeAccessToken($token, $this->provider->serviceName);

		$this->provider->setStorage($tempStorage);

		$this->expectException(UnauthorizedAccessException::class);

		$this->provider->me();
	}

}
