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
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Amazon $provider
 */
#[Group('providerLiveTest')]
#[Provider(Amazon::class)]
final class AmazonAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function assertMeResponse(AuthenticatedUser $user):void{
		$this::assertMatchesRegularExpression('/[a-z\d.]+/i', $user->id);
	}

	public function testMeUnauthorizedAccessException():void{
		$token                    = $this->storage->getAccessToken($this->provider->name);
		// avoid refresh
		$token->expires           = AccessToken::NEVER_EXPIRES;
		$token->refreshToken      = null;
		// invalidate token
		$token->accessToken       = 'Atza|nope'; // amazon tokens are prefixed

		// using a temp storage here so that the local tokens won't be overwritten
		$tempStorage = (new MemoryStorage)->storeAccessToken($token, $this->provider->name);

		$this->provider->setStorage($tempStorage);

		$this->expectException(UnauthorizedAccessException::class);

		$this->provider->me();
	}

}
