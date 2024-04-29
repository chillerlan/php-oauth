<?php
/**
 * Class RedditTest
 *
 * @created      09.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, TokenInvalidate};
use chillerlan\OAuth\Providers\Reddit;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Reddit $provider
 */
#[Provider(Reddit::class)]
class RedditTest extends OAuth2ProviderUnitTestAbstract{

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$token = new AccessToken(['expires' => 42]);

		// Reddit responds with a 204
		$this->setMockResponse($this->responseFactory->createResponse(204));

		$this->provider->storeAccessToken($token);

		$this::assertTrue($this->storage->hasAccessToken($this->provider->name));
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken($this->provider->name));

		// token via param

		// the current token shouldn't be deleted
		$token2 = clone $token;
		$token2->accessToken = 'still here';

		$this->provider->storeAccessToken($token2);

		$this::assertTrue($this->provider->invalidateAccessToken($token));
		$this::assertSame('still here', $this->provider->getStorage()->getAccessToken($this->provider->name)->accessToken);
	}

}
